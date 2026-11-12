<?php
if (!defined('ABSPATH')) { exit; }
final class HPG_History {
    const TYPE='hpg_sync_event';
    public static function register() {
        register_post_type(self::TYPE,['label'=>'GitHub Sync Events','public'=>false,'show_ui'=>false,'show_in_rest'=>false,
            'supports'=>['title'],'capabilities'=>array_fill_keys(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','create_posts'],'manage_options'),'map_meta_cap'=>false]);
    }
    public static function change_counts($files) {
        $uploaded=0;$deleted=0;$folders=[];
        foreach($files as $file) { if(($file['status']??'')==='D') { $deleted++; }else { $uploaded++;$folders[dirname($file['path'])]=true; } }
        return ['uploaded'=>$uploaded,'deleted'=>$deleted,'folders'=>count($folders),'changes'=>count($files)];
    }
    public static function duration_label($data) { return isset($data['elapsed_seconds'])?HPG_Sync::duration($data['elapsed_seconds']):'Not recorded'; }
    public static function record($profile,$action,$ok,$message,$details,$actor) {
        if(get_post_type($profile)!==HPG_Sync::TYPE) { return; }
        $user=$actor?get_userdata($actor):null;
        $owner=get_userdata((int)get_post_field('post_author',$profile));
        $config=HPG_Sync::config($profile); $files=$details['files']??[];
        $data=['profile'=>(int)$profile,'profile_name'=>get_the_title($profile),'action'=>$action,'ok'=>(bool)$ok,'time'=>time(),
            'user_id'=>(int)$actor,'user'=>$user?($user->user_login.' ('.$user->display_name.')'):('Scheduled sync'.($owner?' · owner: '.$owner->user_login:'')),
            'branch'=>!empty($details['branch'])?$details['branch']:$config['branch'],'commit'=>$details['commit']??'',
            'paths'=>$config['paths'],'include_all'=>!empty($config['include_all']),'excludes'=>$config['excludes'],'url'=>$config['url'],'message'=>HPG_Engine::redact($message,$profile),'file_count'=>count($files)];
        $started=(int)get_post_meta($profile,'_hpg_process_started',true);if($started>0 && $started<=$data['time']) { $data['elapsed_seconds']=$data['time']-$started; }
        $data['change_counts']=self::change_counts($files);$pushed=$ok && in_array($action,['push','scheduled push'],true);
        $data['uploaded_files']=$pushed?$data['change_counts']['uploaded']:0;$data['deleted_files']=$pushed?$data['change_counts']['deleted']:0;$data['uploaded_folders']=$pushed?$data['change_counts']['folders']:0;
        if($pushed) {
            $summary=$data['uploaded_files'].' files uploaded · '.$data['uploaded_folders'].' containing folders · '.$data['deleted_files'].' files deleted';
            $data['message']=$summary."\n".$data['message'];
            $result=get_post_meta($profile,'_hpg_result',true);if(is_array($result)) { $result['message']=$data['message'];update_post_meta($profile,'_hpg_result',$result); }
            $process=get_post_meta($profile,'_hpg_process',true);if(is_array($process) && ($process['state']??'')==='completed') { $process['phase']=$action.' completed · '.$summary;update_post_meta($profile,'_hpg_process',$process); }
        }
        $event=wp_insert_post(['post_type'=>self::TYPE,'post_status'=>'private','post_title'=>$action.' · '.$data['profile_name'],'post_author'=>$actor],true);
        if(is_wp_error($event)) { error_log('Harsh GitHub Sync: unable to save history record.'); return; }
        $result=get_post_meta($profile,'_hpg_result',true);$data['outcome']=(($result['level']??'')==='warning')?'warning':($ok?'completed':'failed');
        update_post_meta($event,'_hpg_event',$data);update_post_meta($event,'_hpg_outcome',$data['outcome']);update_post_meta($event,'_hpg_timestamp',$data['time']);
        $json=wp_json_encode($files);
        if(function_exists('gzencode')) {
            $packed=gzencode($json,6);
            if($packed!==false) { update_post_meta($event,'_hpg_files',['encoding'=>'gzip','data'=>base64_encode($packed)]); }
            else { update_post_meta($event,'_hpg_files',['encoding'=>'json','data'=>$json]); }
        } else { update_post_meta($event,'_hpg_files',['encoding'=>'json','data'=>$json]); }
        self::cleanup();
        return $event;
    }
    public static function retention() {
        $value=(string)get_option('hpg_history_retention','7');
        return $value==='custom'?'custom':(in_array($value,['7','15','30','0'],true)?(int)$value:7);
    }
    public static function save_settings() {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.','',['response'=>403]); }
        check_admin_referer('hpg_history_settings');
        $days=(string)($_POST['retention']??'7');
        if(!in_array($days,['7','15','30','0','custom'],true)) { wp_die('Invalid retention period.'); }
        if($days==='custom') {
            $n=(string)($_POST['retain_every']??'1');$unit=(string)($_POST['retain_unit']??'days');
            if(!ctype_digit($n) || (int)$n<1 || (int)$n>1000 || !in_array($unit,['days','months','years'],true)) { wp_die('Custom retention needs 1–1000 and Days, Months, or Years.'); }
            update_option('hpg_history_custom',['every'=>(int)$n,'unit'=>$unit],false);
        }
        update_option('hpg_history_retention',$days,false);
        self::cleanup();
        wp_safe_redirect(admin_url('admin.php?page=hpg-history&settings_saved=1'));exit;
    }
    public static function export_all() {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.','',['response'=>403]); }
        check_admin_referer('hpg_history_all');
        try { $ids=get_posts(['post_type'=>self::TYPE,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids']);$name=HPG_Maintenance::archive('Download all history',$ids); }
        catch(Throwable $e) { wp_die(esc_html($e->getMessage())); }
        HPG_Maintenance::send_archive($name);
    }
    public static function delete_all() {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.','',['response'=>403]); }
        check_admin_referer('hpg_history_delete_all');
        if(function_exists('set_time_limit')) { @set_time_limit(0); }
        $ids=get_posts(['post_type'=>self::TYPE,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids']);
        try { HPG_Maintenance::archive('Delete all history requested',$ids); } catch(Throwable $e) { wp_die(esc_html($e->getMessage())); }
        foreach($ids as $id) { if(!wp_delete_post($id,true)) { wp_die('Unable to delete a history record. The CSV archive is retained.'); } }
        try { HPG_Maintenance::archive('Delete all history completed',[]); } catch(Throwable $e) { wp_die('History deleted; the earlier CSV is saved, but the completion log failed: '.esc_html($e->getMessage())); }
        wp_safe_redirect(admin_url('admin.php?page=hpg-history&history_deleted=1'));exit;
    }
    public static function cutoff($from=null) {
        $retention=self::retention();$now=$from??time();if($retention!=='custom') { return $now-$retention*86400; }
        $c=get_option('hpg_history_custom',['every'=>1,'unit'=>'days']);$n=max(1,min(1000,(int)($c['every']??1)));$unit=$c['unit']??'days';
        $date=(new DateTimeImmutable('@'.$now))->setTimezone(wp_timezone());
        if($unit==='days') { return $date->modify('-'.$n.' days')->getTimestamp(); }
        $day=(int)$date->format('j');
        $target=$unit==='years'?$date->setDate((int)$date->format('Y')-$n,(int)$date->format('n'),1):$date->setDate((int)$date->format('Y'),(int)$date->format('n'),1)->modify('-'.$n.' months');
        return $target->setDate((int)$target->format('Y'),(int)$target->format('n'),min($day,(int)$target->format('t')))->getTimestamp();
    }
    public static function cleanup() {
        $days=self::retention();if(!$days) { return; }
        $old=get_posts(['post_type'=>self::TYPE,'post_status'=>'private','posts_per_page'=>200,'fields'=>'ids','orderby'=>'ID','order'=>'ASC',
            'date_query'=>[['column'=>'post_date_gmt','before'=>gmdate('Y-m-d H:i:s',self::cutoff()),'inclusive'=>false]]]);
        if($old) { try { HPG_Maintenance::archive('Automatic retention deletion',$old); } catch(Throwable $e) { error_log('Harsh GitHub Sync: '.$e->getMessage());return; } }
        foreach($old as $id) { wp_delete_post($id,true); }
        if(count($old)===200 && function_exists('wp_schedule_single_event') && !wp_next_scheduled('hpg_history_cleanup_batch')) { wp_schedule_single_event(time()+60,'hpg_history_cleanup_batch'); }
    }
    public static function files($event) {
        $packed=get_post_meta($event,'_hpg_files',true);
        if(!is_array($packed)) { return []; }
        $json=$packed['data']??'';
        if(($packed['encoding']??'')==='gzip') {
            if(!function_exists('gzdecode')) { return []; }
            $decoded=base64_decode($json,true);$json=$decoded!==false?gzdecode($decoded):false;
        }
        $files=$json!==false?json_decode($json,true):[];
        return is_array($files)?$files:[];
    }
    public static function preview($event,$profile) {
        if(!$event || get_post_type($event)!==self::TYPE) { return; }
        $data=get_post_meta($event,'_hpg_event',true);
        if(!HPG_Access::admin() && ($data['branch']??'main')==='main') { return; }
        if(!is_array($data) || (int)$data['profile']!==(int)$profile || $data['action']!=='preview' || !$data['ok']) { return; }
        $files=self::files($event);$page=max(1,absint($_GET['preview_page']??1));$max=max(1,(int)ceil(count($files)/200));$page=min($page,$max);
        $base=admin_url('admin.php?page=hpg-sync&profile='.(int)$profile);
        ?>
        <section id="hpg-preview-panel" class="hpg-card hpg-preview"><span class="hpg-kicker">UPLOAD PREVIEW</span><h2>Files that will change on GitHub · <?php echo esc_html(count($files)); ?></h2><p class="hpg-help">Branch: <?php echo esc_html($data['branch']); ?> · Checked <?php echo esc_html(wp_date('M j, Y · H:i:s',$data['time'])); ?>. Nothing has been uploaded yet. Unchanged files need no upload. Run Preview again after editing local files or settings.</p>
        <div class="hpg-panel-scroll"><?php if($files): ?><h3>Folder summary</h3><div class="hpg-table-wrap hpg-large-list"><table class="hpg-table"><thead><tr><th>Folder</th><th>Files to upload</th><th>Files to delete</th></tr></thead><tbody><?php foreach(self::folders($files) as $folder=>$counts): ?><tr><td><code><?php echo esc_html($folder); ?></code></td><td><?php echo esc_html($counts['uploaded']); ?></td><td><?php echo esc_html($counts['deleted']); ?></td></tr><?php endforeach; ?></tbody></table></div>
        <h3>Exact file list</h3><div class="hpg-table-wrap"><table class="hpg-table hpg-preview-files"><thead><tr><th>Planned change</th><th>File path</th></tr></thead><tbody><?php foreach(array_slice($files,($page-1)*200,200) as $file): ?><tr><td><?php echo esc_html(['A'=>'Upload new file','M'=>'Upload updated file','D'=>'Delete from GitHub','T'=>'Update file type'][$file['status']]??$file['status']); ?></td><td><code><?php echo esc_html($file['path']); ?></code></td></tr><?php endforeach; ?></tbody></table></div><div class="hpg-pagination"><?php if($page>1): ?><a href="<?php echo esc_url(add_query_arg(['preview_page'=>$page-1],$base)); ?>">← Previous files</a><?php endif; ?><span>File page <?php echo esc_html($page.' / '.$max); ?></span><?php if($page<$max): ?><a href="<?php echo esc_url(add_query_arg(['preview_page'=>$page+1],$base)); ?>">Next files →</a><?php endif; ?></div><p><a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action'=>'hpg_history_csv','event'=>$event],admin_url('admin-post.php')),'hpg_history_csv_'.$event)); ?>">Download complete preview file list (CSV)</a></p>
        <?php else: ?><p>No file changes to upload. Your selected files match the target branch.</p><?php endif; ?></div></section>
        <?php
    }
    public static function folders($files) {
        $folders=[];
        foreach($files as $file) {
            $folder=dirname($file['path']);$folder=$folder==='.'?'(WordPress root)':$folder.'/';
            if(!isset($folders[$folder])) { $folders[$folder]=['uploaded'=>0,'deleted'=>0]; }
            $key=$file['status']==='D'?'deleted':'uploaded';$folders[$folder][$key]++;
        }
        ksort($folders);return $folders;
    }
    public static function export() {
        $event=absint($_GET['event']??0);check_admin_referer('hpg_history_csv_'.$event);
        if(!current_user_can('manage_options')) {
            $data=get_post_meta($event,'_hpg_event',true);
            if(!is_array($data) || $data['action']!=='preview' || !HPG_Access::allowed('push')) { wp_die('History export access denied.','',['response'=>403]); }
            HPG_Access::protect_main([$data['branch']]);HPG_Access::check('preview',HPG_Sync::config((int)$data['profile']));
        }
        if(get_post_type($event)!==self::TYPE) { wp_die('Invalid history event.'); }
        nocache_headers();header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="github-sync-event-'.$event.'.csv"');
        $out=fopen('php://output','w');fputcsv($out,['Status','File path']);
        foreach(self::files($event) as $file) {
            $path=(string)$file['path'];if(preg_match('/^[=+@\-\t\r\n]/',$path)) { $path="'".$path; }
            fputcsv($out,[$file['status'],$path]);
        } fclose($out);exit;
    }
    public static function index_legacy() {
        $ids=get_posts(['post_type'=>self::TYPE,'post_status'=>'private','posts_per_page'=>200,'fields'=>'ids','meta_query'=>[['key'=>'_hpg_outcome','compare'=>'NOT EXISTS']]]);
        foreach($ids as $id) { $d=get_post_meta($id,'_hpg_event',true);if(!is_array($d)) { continue; }update_post_meta($id,'_hpg_outcome',$d['outcome']??(!empty($d['ok'])?'completed':'failed'));update_post_meta($id,'_hpg_timestamp',$d['time']); }
        if(count($ids)===200 && function_exists('wp_schedule_single_event') && !wp_next_scheduled('hpg_history_index')) { wp_schedule_single_event(time()+60,'hpg_history_index'); }
    }
    public static function filters() {
        $status=sanitize_key($_GET['status']??'all');if(!in_array($status,['all','completed','failed','warning'],true)) { $status='all'; }
        $order=strtoupper(sanitize_key($_GET['sort']??'desc'));if(!in_array($order,['ASC','DESC'],true)) { $order='DESC'; }
        $out=['status'=>$status,'sort'=>strtolower($order),'from'=>'','to'=>''];
        foreach(['from','to'] as $key) { $raw=(string)($_GET[$key]??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$raw)) { continue; }$d=DateTimeImmutable::createFromFormat('!Y-m-d',$raw,wp_timezone());if($d && $d->format('Y-m-d')===$raw) { $out[$key]=$raw; } }
        if($out['from'] && $out['to'] && $out['from']>$out['to']) { $temp=$out['from'];$out['from']=$out['to'];$out['to']=$temp; }return $out;
    }
    public static function query_args($filters,$page=1,$status=null) {
        $args=['post_type'=>self::TYPE,'post_status'=>'private','posts_per_page'=>25,'paged'=>$page,'orderby'=>'ID','order'=>strtoupper($filters['sort'])];$meta=[];$state=$status??$filters['status'];
        if($state!=='all') { $meta[]=['key'=>'_hpg_outcome','value'=>$state]; }
        if($filters['from']) { $d=new DateTimeImmutable($filters['from'].' 00:00:00',wp_timezone());$meta[]=['key'=>'_hpg_timestamp','value'=>$d->getTimestamp(),'compare'=>'>=','type'=>'NUMERIC']; }
        if($filters['to']) { $d=new DateTimeImmutable($filters['to'].' 00:00:00',wp_timezone());$meta[]=['key'=>'_hpg_timestamp','value'=>$d->modify('+1 day')->getTimestamp(),'compare'=>'<','type'=>'NUMERIC']; }
        if($meta) { $args['meta_query']=$meta; }return $args;
    }
    public static function pagination($page,$max,$total,$key='history_page',$extra=[]) {
        $max=max(1,(int)$max);$filters=self::filters();$base=array_merge($filters,$extra);
        ?><nav class="hpg-number-pages" aria-label="History pagination"><div class="hpg-page-circles"><?php foreach(array_unique(array_merge([1],range(max(1,$page-2),min($max,$page+2)),[$max])) as $n): ?><a class="<?php echo $n===$page?'active':''; ?>" <?php echo $n===$page?'aria-current="page"':''; ?> href="<?php echo esc_url(self::url(array_merge($base,[$key=>$n]))); ?>"><?php echo esc_html($n); ?></a><?php endforeach; ?></div><div class="hpg-page-jump"><a href="<?php echo esc_url(self::url(array_merge($base,[$key=>1]))); ?>">First</a><span>Page <?php echo esc_html($page.' of '.$max); ?> · <?php echo esc_html($total); ?> records</span><a href="<?php echo esc_url(self::url(array_merge($base,[$key=>$max]))); ?>">Last</a></div><form method="get" class="hpg-jump-form"><input type="hidden" name="page" value="hpg-history"><?php foreach($base as $name=>$value): ?><input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"><?php endforeach; ?><label>Go to page <input type="number" name="<?php echo esc_attr($key); ?>" min="1" max="<?php echo esc_attr($max); ?>" value="<?php echo esc_attr($page); ?>"></label><button class="hpg-btn secondary">Go</button></form></nav><?php
    }
    private static function url($args=[]) { return add_query_arg($args,admin_url('admin.php?page=hpg-history')); }
    public static function page() {
        if(!current_user_can('manage_options')) { return; }
        self::cleanup();self::index_legacy();$filters=self::filters();
        $page=max(1,absint($_GET['history_page']??1));$event=absint($_GET['event']??0);
        $query=new WP_Query(self::query_args($filters,$page));$max_pages=max(1,(int)$query->max_num_pages);
        if($page>$max_pages) { $page=$max_pages;$query=new WP_Query(self::query_args($filters,$page)); }
        $counts=[];foreach(['all','completed','failed','warning'] as $state) { $q=new WP_Query(array_merge(self::query_args($filters,1,$state),['posts_per_page'=>1,'fields'=>'ids']));$counts[$state]=(int)$q->found_posts; }
        $overall=new WP_Query(['post_type'=>self::TYPE,'post_status'=>'private','posts_per_page'=>1,'fields'=>'ids']);

        ?>
        <div class="wrap hpg"><div class="hpg-head"><div><span class="hpg-kicker">HARSH PATEL / AUDIT TRAIL</span><h1>Sync History<span class="hpg-dot">.</span></h1><p>Who pushed what, to which branch, and when.</p></div><a class="hpg-history-link" href="<?php echo esc_url(admin_url('admin.php?page=hpg-sync')); ?>">← Back to sync</a></div>
        <?php HPG_Pages::navigation(); ?>
        <section class="hpg-card"><h2>Saved CSV archives</h2><?php HPG_Maintenance::archive_links(); ?></section><section class="hpg-card"><h2>History overview</h2><a class="hpg-btn secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=hpg_history_all'),'hpg_history_all')); ?>">Download all history · CSV</a><p class="hpg-help"><?php echo esc_html((int)$overall->found_posts); ?> total stored events. Counts below respect the selected date range. Status filters apply to the activity table.</p><div class="hpg-history-counts"><?php foreach(['all'=>'Total history','completed'=>'Completed','failed'=>'Failed','warning'=>'Warnings'] as $state=>$label): ?><a href="<?php echo esc_url(self::url(array_merge($filters,['status'=>$state]))); ?>"><strong><?php echo esc_html($counts[$state]); ?></strong><span><?php echo esc_html($label); ?></span></a><?php endforeach; ?></div><form method="get" class="hpg-history-filters"><input type="hidden" name="page" value="hpg-history"><label>Status<select name="status"><?php foreach(['all'=>'All history','completed'=>'Completed','failed'=>'Failed','warning'=>'Warnings'] as $state=>$label): ?><option value="<?php echo esc_attr($state); ?>" <?php selected($filters['status'],$state); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label><label>Order<select name="sort"><?php foreach(['desc'=>'Newest first (DESC)','asc'=>'Oldest first (ASC)'] as $value=>$label): ?><option value="<?php echo esc_attr($value); ?>" <?php selected($filters['sort'],$value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label><label>From date<input type="date" name="from" value="<?php echo esc_attr($filters['from']); ?>"></label><label>To date<input type="date" name="to" value="<?php echo esc_attr($filters['to']); ?>"></label><button class="hpg-btn">Apply filters</button><a class="hpg-btn secondary" href="<?php echo esc_url(self::url()); ?>">Clear all filters</a></form></section>
        <section class="hpg-card"><span class="hpg-kicker">HISTORY SETTINGS</span><h2>Automatic history deletion</h2>
        <?php if(!empty($_GET['settings_saved'])): ?><div class="hpg-alert success">History settings saved.</div><?php endif; ?>
        <?php if(!empty($_GET['history_deleted'])): ?><div class="hpg-alert success">All plugin history has been deleted.</div><?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="hpg_history_settings"><?php wp_nonce_field('hpg_history_settings'); ?>
        <label for="hpg-retention">Delete history automatically after</label><select id="hpg-retention" name="retention" onchange="document.getElementById('hpg-retention-custom').hidden=this.value!=='custom';"><?php foreach([7=>'7 days',15=>'15 days',30=>'1 month (30 days)',0=>'Never','custom'=>'Custom'] as $days=>$label): ?><option value="<?php echo esc_attr($days); ?>" <?php selected(self::retention(),$days); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select><?php $custom=get_option('hpg_history_custom',['every'=>1,'unit'=>'days']); ?><div id="hpg-retention-custom" <?php echo self::retention()!=='custom'?'hidden':''; ?>><label for="hpg-retain-every">Keep history for</label><input id="hpg-retain-every" name="retain_every" type="number" min="1" max="1000" step="1" value="<?php echo esc_attr($custom['every']??1); ?>"><label for="hpg-retain-unit">Retention interval</label><select id="hpg-retain-unit" name="retain_unit"><?php foreach(['days'=>'Days','months'=>'Months','years'=>'Years'] as $value=>$label): ?><option value="<?php echo esc_attr($value); ?>" <?php selected($custom['unit']??'days',$value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></div>
        <div class="hpg-row"><button class="hpg-btn" type="submit">Save history settings</button></div></form><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Permanently delete ALL plugin history and file lists? This cannot be undone. GitHub commits and sync profiles stay intact.');"><input type="hidden" name="action" value="hpg_history_delete_all"><?php wp_nonce_field('hpg_history_delete_all'); ?><div class="hpg-row"><button class="hpg-btn hpg-delete-history" type="submit">Delete all history now</button></div></form></section>
        <?php if($event && get_post_type($event)===self::TYPE): $data=get_post_meta($event,'_hpg_event',true);$files=self::files($event);$summary=self::change_counts($files);$file_page=max(1,absint($_GET['file_page']??1));$max=max(1,(int)ceil(count($files)/200));$file_page=min($file_page,$max); ?>
        <section class="hpg-card hpg-event"><span class="hpg-kicker">ACTION DETAILS</span><h2><?php echo esc_html(($data['ok']?'Completed':'Failed').' · '.$data['action']); ?></h2>
        <div class="hpg-event-meta"><p><strong>Duration</strong><br><?php echo esc_html(self::duration_label($data)); ?></p><p><strong>File / folder counts</strong><br><?php echo esc_html($summary['uploaded'].((!empty($data['ok']) && in_array($data['action'],['push','scheduled push'],true))?' uploaded files · ':' planned/attempted files · ').$summary['folders'].' containing folders · '.$summary['deleted'].' deleted files'); ?></p><p><strong>Time</strong><br><?php echo esc_html(wp_date('M j, Y · H:i:s',$data['time'])); ?></p><p><strong>User</strong><br><?php echo esc_html($data['user']); ?></p><p><strong>Profile / branch</strong><br><?php echo esc_html($data['profile_name'].' / '.$data['branch']); ?></p><p><strong>Commit</strong><br><?php echo esc_html($data['commit']?:'—'); ?></p></div>
        <label>Selected source folders</label><pre><?php echo esc_html($data['paths']); ?></pre><label>Exclusions at time of action</label><pre><?php echo esc_html($data['excludes']??'(not recorded)'); ?></pre><label>Result</label><pre><?php echo esc_html($data['message']); ?></pre>
        <h3><?php echo esc_html(($data['ok'] && in_array($data['action'],['push','scheduled push'],true))?'Pushed file changes':(($data['ok'] && $data['action']==='accept_pr')?'Merged file changes':'Planned / attempted file changes')); ?> · <?php echo esc_html(count($files)); ?></h3>
        <?php if($files): $pushed=$data['ok'] && in_array($data['action'],['push','scheduled push'],true); ?><h3><?php echo $pushed?'Uploaded folders':'Planned / attempted folders'; ?></h3><div class="hpg-table-wrap hpg-large-list"><table class="hpg-table"><thead><tr><th>Folder</th><th><?php echo $pushed?'Uploaded files':'Added / updated files'; ?></th><th>Deleted files</th></tr></thead><tbody><?php foreach(self::folders($files) as $folder=>$counts): ?><tr><td><code><?php echo esc_html($folder); ?></code></td><td><?php echo esc_html($counts['uploaded']); ?></td><td><?php echo esc_html($counts['deleted']); ?></td></tr><?php endforeach; ?></tbody></table></div><h3>Exact file list</h3><a class="hpg-btn secondary hpg-inline" href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action'=>'hpg_history_csv','event'=>$event],admin_url('admin-post.php')),'hpg_history_csv_'.$event)); ?>">Download full file list (CSV)</a>
        <div class="hpg-table-wrap"><table class="hpg-table"><thead><tr><th>Change</th><th>File / folder path</th></tr></thead><tbody><?php foreach(array_slice($files,($file_page-1)*200,200) as $file): ?><tr><td><?php echo esc_html(['A'=>'Added','M'=>'Modified','D'=>'Deleted','T'=>'Type changed'][$file['status']]??$file['status']); ?></td><td><code><?php echo esc_html($file['path']); ?></code></td></tr><?php endforeach; ?></tbody></table></div>
        <?php self::pagination($file_page,$max,count($files),'file_page',['event'=>$event,'history_page'=>$page]); ?>
        <?php else: ?><?php endif; ?></section>
        <?php endif; ?>
        <section class="hpg-card"><span class="hpg-kicker">RECENT ACTIVITY</span><h2>All sync actions</h2><div class="hpg-table-wrap"><table class="hpg-table"><thead><tr><th>Time</th><th>User</th><th>Profile / branch</th><th>Action</th><th>Result</th><th>Duration</th><th>Files / folders</th><th>Details</th></tr></thead><tbody>
        <?php foreach($query->posts as $post):$data=get_post_meta($post->ID,'_hpg_event',true);if(!is_array($data)){continue;} ?><tr><td><?php echo esc_html(wp_date('M j, Y H:i:s',$data['time'])); ?></td><td><?php echo esc_html($data['user']); ?></td><td><?php echo esc_html($data['profile_name']); ?><br><code><?php echo esc_html($data['branch']); ?></code></td><td><?php echo esc_html($data['action']); ?></td><td><span class="hpg-result-tag"><?php echo esc_html(ucfirst($data['outcome']??($data['ok']?'completed':'failed'))); ?></span></td><td><?php echo esc_html(self::duration_label($data)); ?></td><td><?php $rowcounts=$data['change_counts']??self::change_counts(self::files($post->ID));$pushed=!empty($data['ok']) && in_array($data['action'],['push','scheduled push'],true);echo esc_html($rowcounts['uploaded'].($pushed?' uploaded':' added/updated').' · '.$rowcounts['folders'].' folders · '.$rowcounts['deleted'].' deleted'); ?></td><td><a href="<?php echo esc_url(self::url(array_merge($filters,['event'=>$post->ID,'history_page'=>$page]))); ?>">View files ↗</a></td></tr><?php endforeach; ?>
        <?php if(!$query->posts): ?><tr><td colspan="8">No history yet. Run your next sync action to start the audit trail.</td></tr><?php endif; ?>
        </tbody></table></div><?php self::pagination($page,max(1,$query->max_num_pages),(int)$query->found_posts); ?></section></div>
        <?php
    }
}
