<?php
/**
 * Plugin Name: Harsh GitHub Sync
 * Description: Push selected WordPress code to GitHub using HTTPS or SSH, with branches, previews and scheduled sync profiles.
 * Version: 1.0
 * Author: Harsh Patel
 * Author URI: https://github.com/patelharsh16320
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: harsh-github-sync
 * Domain Path: /languages
 */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/includes/engine.php';
require_once __DIR__ . '/includes/history.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/github.php';
require_once __DIR__ . '/includes/access.php';
require_once __DIR__ . '/includes/pages.php';
require_once __DIR__ . '/includes/maintenance.php';
final class HPG_Sync {
    const TYPE = 'hpg_sync_profile';
    public function __construct() {
        add_action('init', [$this, 'register']);
        add_action('admin_init',[$this,'setup_redirect']);
        add_action('admin_post_hpg_reset',['HPG_Maintenance','reset']);
        add_action('admin_post_hpg_log',['HPG_Maintenance','download']);
        add_action('admin_post_hpg_settings',['HPG_Settings','save']);
        add_action('wp_ajax_hpg_process',[$this,'process_status']);
        add_action('wp_ajax_hpg_reveal_token',[$this,'reveal_token']);
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_post_hpg_sync', [$this, 'handle']);
        add_action('admin_post_hpg_history_delete_all', ['HPG_History','delete_all']);
        add_action('admin_post_hpg_history_settings', ['HPG_History','save_settings']);
        add_action('hpg_history_index',['HPG_History','index_legacy']);
        add_action('hpg_history_cleanup_batch', ['HPG_History','cleanup']);
        add_action('hpg_history_cleanup', ['HPG_History','cleanup']);
        add_action('admin_post_hpg_history_all',['HPG_History','export_all']);
        add_action('admin_post_hpg_history_csv', ['HPG_History','export']);
        add_filter('map_meta_cap',['HPG_Access','capability'],10,4);
        add_filter('cron_schedules', [$this, 'schedules']);
        add_action('hpg_sync_tick', [$this, 'cron'], 10, 1);
    }
    public function register() {
        HPG_History::register();
        if(function_exists('wp_next_scheduled') && !wp_next_scheduled('hpg_history_cleanup')) { wp_schedule_event(time()+60,'hourly','hpg_history_cleanup'); }
        register_post_type(self::TYPE, ['label'=>'GitHub Sync Profiles', 'public'=>false,
            'show_ui'=>false, 'show_in_rest'=>false, 'supports'=>['title'],
            'capabilities'=>array_fill_keys(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','create_posts'], 'manage_options'),
            'map_meta_cap'=>false]);
    }
    public static function duration($seconds) { $n=max(0,(int)$seconds);return intdiv($n,60).'m '.($n%60).'sec'; }
    public static function save_token($id,$input) {
        if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may update repository credentials.'); }
        $token=trim(wp_unslash($input['token']??''));$clear=!empty($input['clear_token']);
        if($token!=='' && $clear) { throw new RuntimeException('Token was not changed: uncheck Remove stored token before saving a new token.'); }
        if($clear) { delete_post_meta($id,'_hpg_token');delete_post_meta($id,'_hpg_armed');return 'Stored token removed.'; }
        if($token==='') { return get_post_meta($id,'_hpg_token',true)?'Existing stored token kept.':'No token stored for this profile. Add one for merge tools.'; }
        if($token==='********') { throw new RuntimeException('The mask is not a token. Leave the input blank to keep the stored token, or paste a new GitHub token.'); }
        if(preg_match('/\s/',$token)) { throw new RuntimeException('Token contains whitespace. Paste only the generated token.'); }
        $sealed=HPG_Engine::seal($token);update_post_meta($id,'_hpg_token',$sealed);
        if(!hash_equals($token,HPG_Engine::token($id))) { throw new RuntimeException('Token could not be verified after saving. Check database access and WordPress salts.'); }
        delete_post_meta($id,'_hpg_armed');return 'Token saved and verified for profile '.get_the_title($id).'. The input is blank after saving; the stored token remains.';
    }
    public static function file_config($c,$input) {
        $c['paths']=HPG_Engine::normalize_paths(trim(wp_unslash($input['paths']??'*')));
        $c['excludes']=trim(wp_unslash($input['excludes']??''));$c['include_all']=!empty($input['include_all'])?1:0;
        $preset=(string)($input['size_preset']??'50');if(!in_array($preset,['50','100','custom','unlimited'],true)) { throw new RuntimeException('Invalid file size option.'); }
        $raw=$preset==='custom'?trim(wp_unslash($input['size_mb']??'')):$preset;
        if($raw==='unlimited') { $c['size_mb']=0; }elseif(ctype_digit($raw) && (float)$raw>=10 && (float)$raw<=PHP_INT_MAX/1048576) { $c['size_mb']=(int)$raw; }else { throw new RuntimeException('Custom size must be at least 10 MiB.'); }
        $mode=(string)($input['large_mode']??'ask');if(!in_array($mode,['ask','skip','upload'],true)) { throw new RuntimeException('Invalid large-file choice.'); }$c['large_mode']=$mode;$c['auto_large']=!empty($input['auto_large'])?1:0;
        HPG_Engine::validate_config($c);return $c;
    }
    public static function default_name() {
        $name=trim(sanitize_text_field(get_option('blogname','')));return $name!==''?$name:'My WordPress Site';
    }
    public static function activate() {
        if(current_user_can('manage_options')) { set_transient('hpg_setup_'.get_current_user_id(),1,300); }
    }
    public function setup_redirect() {
        if(!current_user_can('manage_options') || wp_doing_ajax() || is_network_admin() || ($_SERVER['REQUEST_METHOD']??'GET')!=='GET' || !empty($_GET['activate-multi'])) { return; }
        $pending=get_transient('hpg_setup_'.get_current_user_id());$push=($_GET['page']??'')==='hpg-sync';
        if($push && !empty($_GET['reset_done']) && !$pending) { return; }
        if(!$pending && !$push) { return; }
        if($pending) { delete_transient('hpg_setup_'.get_current_user_id()); }
        if(get_posts(['post_type'=>self::TYPE,'post_status'=>'private','numberposts'=>1,'fields'=>'ids'])) { return; }
        wp_safe_redirect(admin_url('admin.php?page=hpg-profiles&profile=0'));exit;
    }
    public function menu() {
        add_menu_page(__('GitHub Sync', 'harsh-github-sync'), __('GitHub Sync', 'harsh-github-sync'), 'hpg_tools', 'hpg-sync', ['HPG_Pages','push'], 'dashicons-update-alt', 80);
        add_submenu_page('hpg-sync',__('Git Review & Merge', 'harsh-github-sync'),__('Git Review & Merge', 'harsh-github-sync'),'hpg_tools','hpg-git',['HPG_Pages','git']);
        add_submenu_page('hpg-sync',__('Profiles', 'harsh-github-sync'),__('Profile Settings', 'harsh-github-sync'),'manage_options','hpg-profiles',[$this,'profile_page']);
        add_submenu_page('hpg-sync',__('Settings', 'harsh-github-sync'),__('Settings', 'harsh-github-sync'),'manage_options','hpg-settings',['HPG_Settings','page']);
        add_submenu_page('hpg-sync','Sync History','Sync History','manage_options','hpg-history',['HPG_History','page']);
    }
    public function assets($hook) {
        if ($hook === 'toplevel_page_hpg-sync' || strpos($hook,'hpg-history')!==false || strpos($hook,'hpg-settings')!==false || strpos($hook,'hpg-git')!==false || strpos($hook,'hpg-profiles')!==false) { wp_enqueue_style('hpg-sync', plugins_url('assets/admin.css', __FILE__), [], '1.0');wp_enqueue_script('hpg-sync',plugins_url('assets/admin.js',__FILE__),[],'1.0',true);wp_localize_script('hpg-sync','hpgProcess',['url'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('hpg_process')]);$ui=get_option('hpg_ui_settings',[]);wp_add_inline_style('hpg-sync','.hpg{--hpg-panel-height:'.max(30,min(90,(int)($ui['preview_height']??70))).'vh}'); }
    }
    public function schedules($items) {
        $items['hpg_15min'] = ['interval'=>900, 'display'=>'Every 15 minutes'];
        return $items;
    }
    public static function config($id) {
        $c = get_post_meta($id, '_hpg_config', true);
        $builtins=['url'=>'', 'branch'=>'main',
            'paths'=>'*',
            'excludes'=>"*.wpress\nwp-content/updraft/*.zip", 'include_all'=>1, 'large_mode'=>'ask', 'auto_large'=>0, 'size_mb'=>50, 'timeout_min'=>0, 'schedule'=>'off','schedule_every'=>1,'schedule_unit'=>'days','date_branch'=>0];
        $defaults=get_option('hpg_defaults',[]);$defaults=is_array($defaults)?array_intersect_key($defaults,$builtins):[];
        return wp_parse_args(is_array($c)?$c:[],wp_parse_args($defaults,$builtins));
    }
    public static function consume_notice($id) {
        $result=get_post_meta($id,'_hpg_result',true);
        if(is_array($result)) {
            delete_post_meta($id,'_hpg_result');
            $process=get_post_meta($id,'_hpg_process',true);
            if(is_array($process) && ($process['state']??'')!=='running') { delete_post_meta($id,'_hpg_process'); }
        }
        return $result;
    }
    public static function process_data($id) {
        $p=get_post_meta($id,'_hpg_process',true);
        if(is_array($p) && $p['state']==='running' && time()-$p['time']>120 && !HPG_Engine::is_running($id)) { $p['state']='unknown';$p['phase']='No active Git lock or recent update. Check History before retrying.'; }
        if(is_array($p)) { $start=(int)get_post_meta($id,'_hpg_process_started',true);if($start) { $p['elapsed']=max(0,($p['state']==='running'?time():(int)$p['time'])-$start); } }
        return $p;
    }
    public function reveal_token() {
        if(!current_user_can('manage_options')) { wp_send_json_error(['message'=>'Administrator access required.'],403); }
        check_ajax_referer('hpg_reveal_token','nonce');$id=absint($_POST['profile']??0);
        if(!$id || get_post_type($id)!==self::TYPE) { wp_send_json_error(['message'=>'Invalid profile.'],400); }
        nocache_headers();header('Cache-Control: no-store, private');
        try { $token=HPG_Engine::token($id);if($token==='') { throw new RuntimeException('No token is stored for this profile. Save a token first.'); } }
        catch(Throwable $e) { wp_send_json_error(['message'=>HPG_Engine::redact($e->getMessage(),$id)],400); }
        wp_send_json_success(['token'=>$token]);
    }
    public function process_status() {
        if(!HPG_Access::allowed('push') && !HPG_Access::allowed('merge')) { wp_send_json_error('Administrator access required.',403); }check_ajax_referer('hpg_process','nonce');
        $id=absint($_GET['profile']??0);if(get_post_type($id)!==self::TYPE) { wp_send_json_error('Invalid profile.',400); }
        wp_send_json_success(self::process_data($id));
    }
    public static function finish($page,$id,$task='') {
        $url=admin_url('admin.php?page='.$page.($id?'&profile='.$id:'')).($task==='scan_large'?'&scan=1':'').($task==='preview'?'#hpg-preview-panel':'');
        if(!empty($_POST['hpg_ajax'])) { wp_send_json_success(['redirect'=>$url,'result'=>$id?get_post_meta($id,'_hpg_result',true):null]); }
        wp_safe_redirect($url);exit;
    }
    public function handle() {
        if(!HPG_Access::allowed('push') && !HPG_Access::allowed('merge') && !HPG_Access::admin()) { wp_die('Git access denied.','',['response'=>403]); }
        check_admin_referer('hpg_sync');
        $id = absint($_POST['profile'] ?? 0);
        if ($id && get_post_type($id)!==self::TYPE) { wp_die('Invalid profile.'); }
        $task = sanitize_key($_POST['task'] ?? 'save');
        $engine=null;$api=null;
        $return=sanitize_key($_POST['return_page']??'hpg-profiles');if(!in_array($return,['hpg-sync','hpg-git','hpg-profiles'],true)) { $return='hpg-sync'; }
        if (!in_array($task, ['save','test','fetch','preview','push','key','permissions','delete','branches','create_branch','delete_branch','scan_large','delete_key','regenerate_key','duplicate','save_files','save_schedule','save_token','test_api','find_pr','compare','create_pr','review_pr','accept_pr','load_conflicts','resolve_conflicts'], true)) { wp_die('Invalid action.'); }
        if(!HPG_Access::admin()) { $return=in_array($task,['compare','create_pr','review_pr','accept_pr','load_conflicts','resolve_conflicts','find_pr','branches'],true)?'hpg-git':'hpg-sync'; }
        try {
            HPG_Access::check($task,self::config($id));
            if($id && HPG_Engine::is_running($id)) { throw new HPG_Operation_Running(HPG_Engine::running_message($id)); }
            if($id) { update_post_meta($id,'_hpg_process_started',time()); }
            if($task==='save_token') {
                if(!$id) { throw new RuntimeException('Select a saved profile.'); }
                $message=self::save_token($id,$_POST);update_post_meta($id,'_hpg_result',['ok'=>true,'time'=>time(),'action'=>$task,'message'=>$message]);update_post_meta($id,'_hpg_process',['state'=>'completed','phase'=>'Token saved','time'=>time()]);
                HPG_History::record($id,$task,true,$message,[],get_current_user_id());self::finish($return,$id,$task);
            }
            if(in_array($task,['push','preview','branches','fetch','scan_large','save_files','create_branch','delete_branch','save_schedule','permissions'],true) && (isset($_POST['return_page']) || !HPG_Access::admin()) && ($task!=='create_branch' || $return==='hpg-sync')) {
                if(!$id) { throw new RuntimeException('Select a saved profile.'); }
                $c=self::config($id);
                if($task==='delete_branch' && trim(wp_unslash($_POST['delete_confirm']??''))!==trim(wp_unslash($_POST['delete_branch']??''))) { throw new RuntimeException('Type the branch name again to confirm deletion.'); }
                if($task==='save_schedule') {
                    if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may update upload schedules.'); }
                    $schedule=sanitize_key($_POST['schedule']??'off');$unit=sanitize_key($_POST['schedule_unit']??'days');$every=trim(wp_unslash($_POST['schedule_every']??'1'));
                    if(!in_array($schedule,['off','hpg_15min','hourly','daily','custom'],true) || !in_array($unit,['days','months','years'],true) || !ctype_digit($every) || (int)$every<1 || (int)$every>1000) { throw new RuntimeException('Enter a valid upload schedule.'); }
                    $c['schedule']=$schedule;$c['schedule_every']=(int)$every;$c['schedule_unit']=$unit;$c['date_branch']=!empty($_POST['date_branch'])?1:0;
                    update_post_meta($id,'_hpg_config',$c);delete_post_meta($id,'_hpg_armed');wp_clear_scheduled_hook('hpg_sync_tick',[$id]);
                }
                if(HPG_Access::admin() && isset($_POST['paths']) && in_array($task,['push','preview','scan_large','save_files'],true)) {
                    $updated=self::file_config($c,$_POST);
                    if($updated!==$c) { update_post_meta($id,'_hpg_config',$updated);delete_post_meta($id,'_hpg_armed');wp_clear_scheduled_hook('hpg_sync_tick',[$id]); }
                    $c=$updated;
                }
                if(in_array($task,['push','preview','fetch','create_branch'],true) && isset($_POST['push_branch'])) {
                    $chosen=trim(wp_unslash($_POST['push_branch']));if($task==='create_branch' || empty($c['date_branch']) || $chosen!==wp_date('Y-m-d')) { $c['branch']=$chosen;$c['date_branch']=0; }HPG_Engine::validate_config($c);HPG_Access::check($task,$c);
                }
                if(HPG_Access::admin() && isset($_POST['upload_only'])) { $c['upload_only']=trim(wp_unslash($_POST['upload_only']));update_post_meta($id,'_hpg_upload_only',$c['upload_only']); }
                if($task==='create_branch') { $c['branch_mode']=sanitize_key($_POST['branch_mode']??'empty');$c['branch_source_url']=trim(wp_unslash($_POST['branch_source_url']??''));$c['branch_source_name']=trim(wp_unslash($_POST['branch_source_name']??'main')); }
                update_post_meta($id,'_hpg_process_started',time());$engine=new HPG_Engine($id,$c);$result=$task==='save_schedule'?'Upload schedule saved. Push once to enable automatic uploads.':($task==='save_files'?'File selection saved. Preview upload before pushing.':$engine->perform($task,$task==='create_branch'?trim(wp_unslash($_POST['new_branch']??'')):($task==='delete_branch'?trim(wp_unslash($_POST['delete_branch']??'')):''),true));
                if($task==='create_branch') { $chosen=$engine->details()['branch'];$saved=self::config($id);$saved['branch']=$chosen;$saved['date_branch']=0;update_post_meta($id,'_hpg_config',$saved);delete_post_meta($id,'_hpg_armed');wp_clear_scheduled_hook('hpg_sync_tick',[$id]); }
                if($task==='push') { update_post_meta($id,'_hpg_armed',1);$this->schedule($id,self::config($id)); }
                update_post_meta($id,'_hpg_result',['ok'=>true,'level'=>($task==='scan_large' && get_post_meta($id,'_hpg_large_files',true))?'warning':'success','time'=>time(),'action'=>$task,'message'=>$result]);
                update_post_meta($id,'_hpg_process',['state'=>'completed','phase'=>$task.' completed','time'=>time()]);
                $event=HPG_History::record($id,$task,true,$result,$engine->details(),get_current_user_id());
                if($task==='preview' && $event) { update_post_meta($id,'_hpg_preview_event',$event); }
                self::finish($return,$id,$task);
            }
            if(in_array($task,['compare','create_pr','review_pr','accept_pr','load_conflicts','resolve_conflicts','test_api','find_pr'],true)) {
                if(!$id) { throw new RuntimeException('Save a profile before using Git tools.'); }
                if(HPG_Access::admin() && trim(wp_unslash($_POST['token']??''))!=='') { self::save_token($id,$_POST); }
                $api=new HPG_GitHub($id);$message=$api->run($task,$_POST);
                update_post_meta($id,'_hpg_result',['ok'=>true,'time'=>time(),'action'=>$task,'message'=>$message]);
                update_post_meta($id,'_hpg_process',['state'=>'completed','phase'=>$task.' completed','time'=>time()]);
                HPG_History::record($id,$task,true,$message,$api->details(),get_current_user_id());
                self::finish($return,$id,$task);
            }
            if($task==='duplicate') {
                if(!$id) { throw new RuntimeException('Save a profile before duplicating it.'); }
                $copy=self::config($id);$copy['schedule']='off';
                $new=wp_insert_post(['post_type'=>self::TYPE,'post_status'=>'private','post_title'=>get_the_title($id).'-copy','post_author'=>get_current_user_id()],true);
                if(is_wp_error($new)) { throw new RuntimeException($new->get_error_message()); }
                update_post_meta($new,'_hpg_config',$copy);
                update_post_meta($new,'_hpg_result',['ok'=>true,'time'=>time(),'action'=>'duplicate','message'=>'Profile copied. Add a token or generate a new SSH key; automatic push is off.']);
                self::finish('hpg-profiles',$new,'duplicate');
            }
            if ($task==='delete') {
                wp_clear_scheduled_hook('hpg_sync_tick', [$id]);
                HPG_Engine::remove_profile($id);
                wp_delete_post($id, true);
                self::finish('hpg-profiles',0,'delete');
            }
            if($return==='hpg-profiles' && !isset($_POST['paths'])) {
                $c=self::config($id);$c['url']=trim(wp_unslash($_POST['url']??''));$c['paths']=HPG_Engine::normalize_paths($c['paths']);
            } else {
            $c = ['url'=>trim(wp_unslash($_POST['url'] ?? '')), 'branch'=>trim(wp_unslash($_POST['branch'] ?? 'main')),
                'paths'=>HPG_Engine::normalize_paths(trim(wp_unslash($_POST['paths'] ?? ''))), 'excludes'=>trim(wp_unslash($_POST['excludes'] ?? '')),
                'schedule'=>sanitize_key($_POST['schedule'] ?? 'off'), 'include_all'=>!empty($_POST['include_all'])?1:0,'date_branch'=>!empty($_POST['date_branch'])?1:0,'large_mode'=>sanitize_key($_POST['large_mode']??'ask')];
            $c['timeout_min']=0; // No plugin action deadline.
            $preset=sanitize_key($_POST['size_preset']??'custom');
            if(!in_array($preset,['50','100','custom','unlimited'],true)) { throw new RuntimeException('Invalid file size option.'); }
            $raw_size=$preset==='custom'?trim(wp_unslash($_POST['size_mb']??'50')):$preset;
            if($raw_size==='unlimited') { $c['size_mb']=0; }
            elseif(ctype_digit($raw_size) && (float)$raw_size>=10 && (float)$raw_size<=PHP_INT_MAX/1048576) { $c['size_mb']=(int)$raw_size; }
            else { throw new RuntimeException('Enter a file size of at least 10 MiB, or unlimited.'); }
            if(!in_array($c['large_mode'],['ask','skip','upload'],true)) { $c['large_mode']='ask'; }
            if (!in_array($c['schedule'], ['off','hpg_15min','hourly','daily','custom'], true)) { $c['schedule']='off'; }
            $every=trim(wp_unslash($_POST['schedule_every']??'1'));$unit=sanitize_key($_POST['schedule_unit']??'days');
            if(!ctype_digit($every) || (int)$every<1 || (int)$every>1000 || !in_array($unit,['days','months','years'],true)) { throw new RuntimeException('Custom schedule needs a whole number from 1 to 1000 and Days, Months, or Years.'); }
            $c['schedule_every']=(int)$every;$c['schedule_unit']=$unit;
            }
            HPG_Engine::validate_config($c);
            $old = $id ? self::config($id) : [];
            $saved_id = wp_insert_post(['ID'=>$id, 'post_type'=>self::TYPE, 'post_status'=>'private',
                'post_title'=>sanitize_text_field(wp_unslash($_POST['title'] ?? self::default_name()))], true);
            if (is_wp_error($saved_id)) { throw new RuntimeException($saved_id->get_error_message()); }
            $id = $saved_id;
            update_post_meta($id, '_hpg_config', $c);
            if ($old !== $c) { delete_post_meta($id, '_hpg_armed'); }
            $token_message=self::save_token($id,$_POST);
            if(in_array($task,['delete_key','regenerate_key'],true)) { delete_post_meta($id,'_hpg_armed');wp_clear_scheduled_hook('hpg_sync_tick',[$id]); }
            $engine = new HPG_Engine($id, $c);
            $result = $task==='save' ? 'Profile saved. '.$token_message.' Run Test API access on Git Review to verify repository access.' : $engine->perform($task,trim(wp_unslash($_POST['new_branch'] ?? '')),true);
            if($task==='create_branch') { $c['branch']=$engine->details()['branch'];update_post_meta($id,'_hpg_config',$c);delete_post_meta($id,'_hpg_armed'); }
            if ($task==='push') { update_post_meta($id, '_hpg_armed', 1); }
            $this->schedule($id, $c);
            update_post_meta($id, '_hpg_result', ['ok'=>true,'level'=>($task==='scan_large' && get_post_meta($id,'_hpg_large_files',true))?'warning':'success','time'=>time(),'action'=>$task,'message'=>$result]);
            update_post_meta($id,'_hpg_process',['state'=>'completed','phase'=>$task.' completed','time'=>time()]);
            $event=HPG_History::record($id,$task,true,$result,$engine?$engine->details():[],get_current_user_id());
            if($task==='preview' && $event) { update_post_meta($id,'_hpg_preview_event',$event); }
        } catch (Throwable $e) {
            $message = HPG_Engine::redact($e->getMessage(), $id);
            if($e instanceof HPG_Operation_Running) { if($id) { update_post_meta($id,'_hpg_result',['ok'=>false,'level'=>'warning','time'=>time(),'action'=>$task,'message'=>$message]); }self::finish($return,$id,$task); }
            if ($id) { update_post_meta($id,'_hpg_process',['state'=>'failed','phase'=>substr($message,0,240),'time'=>time()]);update_post_meta($id, '_hpg_result', ['ok'=>false,'level'=>$e instanceof HPG_Review_Required?'warning':'error','time'=>time(),'action'=>$task,'message'=>$message]);
                HPG_History::record($id,$task,false,$message,$api?$api->details():($engine?$engine->details():[]),get_current_user_id()); }
            else { set_transient('hpg_error_'.get_current_user_id(), $message, 60); }
        }
        self::finish($return,$id,$task);
    }
    public static function next_custom($c,$from=null) {
        $n=max(1,min(1000,(int)($c['schedule_every']??1)));$unit=$c['schedule_unit']??'days';
        $date=(new DateTimeImmutable('@'.($from??time())))->setTimezone(wp_timezone());
        if($unit==='days') { return $date->modify('+'.$n.' days')->getTimestamp(); }
        $day=(int)$date->format('j');
        if($unit==='years') { $target=$date->setDate((int)$date->format('Y')+$n,(int)$date->format('n'),1); }
        else { $target=$date->setDate((int)$date->format('Y'),(int)$date->format('n'),1)->modify('+'.$n.' months'); }
        return $target->setDate((int)$target->format('Y'),(int)$target->format('n'),min($day,(int)$target->format('t')))->getTimestamp();
    }
    private function schedule($id, $c) {
        wp_clear_scheduled_hook('hpg_sync_tick', [$id]);
        if ($c['schedule']!=='off' && get_post_meta($id,'_hpg_armed',true)) {
            if($c['schedule']==='custom') { wp_schedule_single_event(self::next_custom($c),'hpg_sync_tick',[$id]); }
            else { wp_schedule_event(time()+60, $c['schedule'], 'hpg_sync_tick', [$id]); }
        }
    }
    public function cron($id) {
        if (get_post_type($id)!==self::TYPE || !get_post_meta($id,'_hpg_armed',true)) { return; }
        $c = self::config($id);
        if ($c['schedule']==='off') { return; }
        $owner=get_userdata((int)get_post_field('post_author',$id));
        if(!HPG_Access::allowed('push',$owner) || (!user_can($owner,'manage_options') && ($c['branch']==='main'))) { wp_clear_scheduled_hook('hpg_sync_tick',[$id]);return; }
         if(HPG_Engine::is_running($id)) { if($c['schedule']==='custom' && get_post_meta($id,'_hpg_armed',true)) { $this->schedule($id,$c); }return; }
        update_post_meta($id,'_hpg_process_started',time());$engine=new HPG_Engine($id,$c);
        try { $message=$engine->perform('push'); $ok=true; }
        catch (HPG_Operation_Running $e) { if($c['schedule']==='custom' && get_post_meta($id,'_hpg_armed',true)) { $this->schedule($id,$c); }return; }
        catch (Throwable $e) { $message=HPG_Engine::redact($e->getMessage(),$id); $ok=false; }
        update_post_meta($id,'_hpg_result',['ok'=>$ok,'time'=>time(),'action'=>'scheduled push','message'=>$message]);
        update_post_meta($id,'_hpg_process',['state'=>$ok?'completed':'failed','phase'=>$ok?'Scheduled push completed':substr($message,0,240),'time'=>time()]);
        HPG_History::record($id,'scheduled push',$ok,$message,$engine->details(),0);
        if($c['schedule']==='custom' && get_post_meta($id,'_hpg_armed',true)) { $this->schedule($id,self::config($id)); }
    }
    public function profile_page() {
        if (!current_user_can('manage_options')) { return; }
        $profiles=get_posts(['post_type'=>self::TYPE,'post_status'=>'private','numberposts'=>-1]);
        $id=absint($_GET['profile']??0);
        if (!isset($_GET['profile']) && $profiles) { $id=$profiles[0]->ID; }
        if ($id && get_post_type($id)!==self::TYPE) { $id=0; }
        $c=self::config($id); $r=self::consume_notice($id);
        $error=get_transient('hpg_error_'.get_current_user_id()); delete_transient('hpg_error_'.get_current_user_id());
        $large_files=get_post_meta($id,'_hpg_large_files',true);$large_scanned=get_post_meta($id,'_hpg_large_time',true);
        $branches=get_post_meta($id,'_hpg_branches',true); $pub=get_post_meta($id,'_hpg_public_key',true);
        ?>
        <div class="wrap hpg"><div class="hpg-head"><div><span class="hpg-kicker">HARSH PATEL / DEVELOPER TOOLS</span><h1>Profile settings<span class="hpg-dot">.</span></h1><p>Manage repository access and SSH keys.</p></div><div class="hpg-head-links"><a class="hpg-history-link" href="<?php echo esc_url(admin_url('admin.php?page=hpg-history')); ?>">Sync history ↗</a><a class="hpg-history-link" href="<?php echo esc_url(admin_url('admin.php?page=hpg-settings')); ?>">Settings ↗</a><span class="hpg-badge">v1.0</span></div></div>
        <?php HPG_Pages::navigation(); ?><nav class="hpg-nav" aria-label="Sync profiles"><?php foreach($profiles as $p): ?><a class="<?php echo $id===$p->ID?'selected':''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=hpg-profiles&profile='.$p->ID)); ?>"><?php echo esc_html($p->post_title); ?></a><?php endforeach; ?><a href="<?php echo esc_url(admin_url('admin.php?page=hpg-profiles&profile=0')); ?>">+ New profile</a></nav>
        <?php if($error): ?><div class="hpg-alert error" role="alert"><?php echo esc_html($error); ?></div><?php endif; ?>
        <?php if(is_array($r)): $level=$r['level']??($r['ok']?'success':'error'); ?><div class="hpg-alert <?php echo esc_attr(in_array($level,['success','warning','error'],true)?$level:'error'); ?>" role="status"><strong><?php echo $level==='warning'?'Review needed':($r['ok']?'Completed':'Action failed'); ?> · <?php echo esc_html($r['action']); ?></strong><small><?php echo esc_html(wp_date('M j, Y · H:i:s',$r['time'])); ?></small><pre><?php echo esc_html($r['message']); ?></pre></div><?php endif; ?>
        <?php $process=self::process_data($id); ?><section class="hpg-card hpg-process" data-state="<?php echo esc_attr($process['state']??'ready'); ?>"><strong>Process status</strong><p id="hpg-process-text" role="status"><?php echo esc_html(is_array($process)?($process['state'].' · '.$process['phase'].(isset($process['elapsed'])?' · '.self::duration($process['elapsed']).' elapsed':'').' · Last update: '.wp_date('H:i:s',$process['time'])):'Ready · choose an action'); ?></p></section>

        
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" autocomplete="off">
        <input type="hidden" name="action" value="hpg_sync"><input type="hidden" name="return_page" value="hpg-profiles"><input type="hidden" name="profile" value="<?php echo esc_attr($id); ?>"><?php wp_nonce_field('hpg_sync'); ?>
        <div class="hpg-profile-connection"><main><section class="hpg-card"><span class="hpg-kicker">01 / CONNECT</span><h2>Repository connection</h2>
        <label for="hpg-title">Profile name</label><input id="hpg-title" name="title" required value="<?php echo esc_attr($id?get_the_title($id):self::default_name()); ?>">
        <label for="hpg-url">GitHub repository URL</label><input id="hpg-url" name="url" required placeholder="https://github.com/owner/repository.git" value="<?php echo esc_attr($c['url']); ?>"><p class="hpg-help">HTTPS, git@github.com:owner/repo.git, or ssh://git@github.com/owner/repo.git. GitHub.com only.</p>
        <label for="hpg-token">GitHub API / HTTPS personal access token</label><input id="hpg-token" name="token" type="password" autocomplete="new-password" placeholder="<?php echo get_post_meta($id,'_hpg_token',true)?'******** · saved token, leave blank to keep':'Required to push over HTTPS'; ?>"><?php HPG_Pages::token_eye($id); ?><label class="hpg-check"><input type="checkbox" name="clear_token" value="1"> Remove stored token</label>
        <div class="hpg-row"><button class="hpg-btn secondary" name="task" value="key">Generate SSH key</button><button class="hpg-btn secondary" name="task" value="regenerate_key" onclick="return confirm('Replace this profile SSH key? Add the new public key on GitHub before pushing.');">Regenerate SSH key</button><button class="hpg-btn secondary" name="task" value="delete_key" onclick="return confirm('Delete this profile local SSH key pair and pause automatic push?');">Delete SSH key</button><button class="hpg-btn secondary" name="task" value="test">Test connection</button></div>
        <?php if($pub): ?><label for="hpg-public">SSH public key</label><textarea id="hpg-public" readonly rows="3" onclick="this.select()"><?php echo esc_textarea($pub); ?></textarea><div class="hpg-row"><button type="button" class="hpg-btn secondary" data-hpg-copy="hpg-public">Copy SSH public key</button><span class="hpg-copy-feedback" role="status"></span></div><?php endif; ?>
        <div class="hpg-row"><button class="hpg-btn" name="task" value="save">Save connection</button><a class="hpg-btn secondary" href="<?php echo esc_url(admin_url('admin.php?page=hpg-sync&profile='.$id)); ?>">Open GitHub Sync</a></div><?php if($id): ?><button class="hpg-btn secondary" name="task" value="duplicate" formnovalidate>Duplicate profile</button><button class="hpg-btn hpg-delete-profile" name="task" value="delete" formnovalidate onclick="return confirm('Delete this profile, its stored credentials and its isolated Git workspace? WordPress and GitHub files remain.');">Delete profile</button><?php endif; ?></section></main></div></form>
        <footer class="hpg-footer">Built by Harsh Patel · GitHub Sync · Source code synchronization, with clear results.</footer></div><?php
    }
}
new HPG_Sync();
register_activation_hook(__FILE__,['HPG_Sync','activate']);
add_action('init',function() {
    if(get_option('hpg_scope_version','')!=='2.3.0') {
        $ids=get_posts(['post_type'=>HPG_Sync::TYPE,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids']);
        foreach($ids as $id) { delete_post_meta($id,'_hpg_armed');wp_clear_scheduled_hook('hpg_sync_tick',[$id]); }
        update_option('hpg_scope_version','2.3.0',false);
    }
},20);
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('hpg_history_index');wp_clear_scheduled_hook('hpg_history_cleanup');wp_clear_scheduled_hook('hpg_history_cleanup_batch');
    $ids=get_posts(['post_type'=>HPG_Sync::TYPE,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids']);
    foreach($ids as $id) { wp_clear_scheduled_hook('hpg_sync_tick',[$id]); }
});
