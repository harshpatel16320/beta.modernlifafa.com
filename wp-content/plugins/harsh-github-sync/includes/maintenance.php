<?php
if(!defined('ABSPATH')) { exit; }
final class HPG_Maintenance {
    public static function directories() { return [dirname(__DIR__).'/log',(defined('WP_CONTENT_DIR')?WP_CONTENT_DIR:rtrim(ABSPATH,'/').'/wp-content').'/hpg-sync-private-log',rtrim(sys_get_temp_dir(),'/').'/hpg-logs-'.substr(hash('sha256',ABSPATH),0,16)]; }
    public static function cell($v) { $v=is_scalar($v)?(string)$v:wp_json_encode($v);return preg_match('/^[=+@\-\t\r]/',$v)?"'".$v:$v; }
    public static function archive($action,$ids) {
        $stream=false;$path='';
        foreach(self::directories() as $dir) {
            if(is_link($dir)) { continue; }
            if(!is_dir($dir) && !@mkdir($dir,0700,true)) { continue; }
            @chmod($dir,0700);
            @file_put_contents($dir.'/.htaccess',"Require all denied\nDeny from all\n");@file_put_contents($dir.'/index.php',"<?php http_response_code(403); exit;\n");
            $path=$dir.'/'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(8)).'.csv';$stream=@fopen($path,'x');if($stream) { @chmod($path,0600);break; }
        }
        if(!$stream) { throw new RuntimeException('Cannot save the CSV archive. History/data were not deleted. Make the plugin log directory writable by the PHP user.'); }
        $user=wp_get_current_user();$ok=fputcsv($stream,['record','UTC time','user ID','username','action','event ID','profile','branch','repository','outcome','message','file status','file/folder','commit','source paths','exclusions','include all','file count','event details JSON','elapsed seconds','uploaded files','deleted files','folders containing uploads']);
        $write=function($row) use($stream,&$ok) { if(fputcsv($stream,array_map([__CLASS__,'cell'],array_pad($row,23,'')))===false) { $ok=false; } };
        $write(['audit',gmdate('c'),$user->ID,($user->user_login?:'WP-Cron / system'),$action,'','','','','','Requested by administrator','','']);
        foreach($ids as $id) {
            $d=get_post_meta($id,'_hpg_event',true);if(!is_array($d)) { $d=[]; }
            $row=['event',gmdate('c',$d['time']??time()),$d['user_id']??0,$d['user']??'',$d['action']??'',$id,$d['profile_name']??'',$d['branch']??'',$d['url']??'',$d['outcome']??'',$d['message']??''];
            $extra=[$d['commit']??'',$d['paths']??'',$d['excludes']??'',!empty($d['include_all'])?'yes':'no',$d['file_count']??0,wp_json_encode($d),$d['elapsed_seconds']??'',$d['uploaded_files']??'',$d['deleted_files']??'',$d['uploaded_folders']??''];
            $files=HPG_History::files($id);if(!$files) { $write(array_merge($row,['',''],$extra)); }
            foreach($files as $f) { $write(array_merge($row,[$f['status']??'',$f['path']??''],$extra)); }
        }
        $write(['audit',gmdate('c'),$user->ID,($user->user_login?:'WP-Cron / system'),$action,'','','','','','Archive saved before deletion; '.count($ids).' records','','']);
        if(!fflush($stream)) { $ok=false; }fclose($stream);if(!$ok) { @unlink($path);throw new RuntimeException('CSV write failed. Deletion stopped.'); }
        // Keep a durable copy outside the plugin folder, which WP may replace on upgrade.
        if(defined('WP_CONTENT_DIR')) {
            $backup=WP_CONTENT_DIR.'/hpg-sync-private-log';
            if(!is_link($backup) && (is_dir($backup) || @mkdir($backup,0700,true))) {
                @chmod($backup,0700);@file_put_contents($backup.'/.htaccess',"Require all denied\nDeny from all\n");@file_put_contents($backup.'/index.php',"<?php http_response_code(403); exit;\n");
                $copy=$backup.'/'.basename($path);if($copy!==$path && @copy($path,$copy)) { @chmod($copy,0600); }
            }
        }
        if(strpos($action,'Full plugin reset')!==0) { update_option('hpg_last_archive',['file'=>basename($path),'path'=>$path,'time'=>time(),'records'=>count($ids)],false); }
        return basename($path);
    }
    public static function reset() {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.'); }check_admin_referer('hpg_reset');
        try {
            $ids=get_posts(['post_type'=>HPG_History::TYPE,'post_status'=>get_post_stati(),'numberposts'=>-1,'fields'=>'ids']);self::archive('Full plugin reset requested',$ids);
            foreach(get_posts(['post_type'=>HPG_Sync::TYPE,'post_status'=>get_post_stati(),'numberposts'=>-1,'fields'=>'ids']) as $id) { HPG_Engine::remove_profile($id);wp_clear_scheduled_hook('hpg_sync_tick',[$id]);if(!wp_delete_post($id,true)) { throw new RuntimeException('Profile deletion failed; reset incomplete.'); } }
            foreach($ids as $id) { if(!wp_delete_post($id,true)) { throw new RuntimeException('History deletion failed; reset incomplete.'); } }
            foreach(['hpg_defaults','hpg_ui_settings','hpg_permissions','hpg_history_retention','hpg_history_custom','hpg_scope_version','hpg_history_index_cursor','hpg_last_archive'] as $key) { if(strpos($key,'_transient_timeout_')===0) { delete_transient(substr($key,19)); }elseif(strpos($key,'_transient_')===0) { delete_transient(substr($key,11)); }delete_option($key); }
            foreach(['hpg_sync_tick','hpg_history_index','hpg_history_cleanup','hpg_history_cleanup_batch'] as $hook) { wp_unschedule_hook($hook); }
            global $wpdb;
            $patterns=[$wpdb->esc_like('hpg_').'%', $wpdb->esc_like('_transient_hpg_').'%', $wpdb->esc_like('_transient_timeout_hpg_').'%'];
            foreach($patterns as $pattern) { foreach($wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$pattern)) as $key) { if(strpos($key,'_transient_timeout_')===0) { delete_transient(substr($key,19)); }elseif(strpos($key,'_transient_')===0) { delete_transient(substr($key,11)); }delete_option($key); } }
            delete_transient('hpg_setup_'.get_current_user_id());delete_transient('hpg_error_'.get_current_user_id());
            self::archive('Full plugin reset completed',[]);
        } catch(Throwable $e) { wp_die(esc_html($e->getMessage())); }
        wp_safe_redirect(admin_url('admin.php?page=hpg-sync&reset_done=1'));exit;
    }
    public static function download() {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.'); }check_admin_referer('hpg_log');
        self::send_archive((string)($_GET['file']??''));
    }
    public static function send_archive($name) {
        if(!current_user_can('manage_options')) { wp_die('Administrator access required.'); }
        if(!preg_match('/^\d{8}-\d{6}-[a-f0-9]{16}\.csv$/D',$name)) { wp_die('Invalid archive.'); }
        foreach(self::directories() as $dir) { $p=$dir.'/'.$name;if(is_file($p) && !is_link($p)) { header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$name.'"');header('Cache-Control: no-store');readfile($p);exit; } }wp_die('Archive not found.');
    }
    public static function archive_links() {
        $last=get_option('hpg_last_archive',[]);if(is_array($last) && !empty($last['path'])) { ?><p class="hpg-help">Last archive saved: <code><?php echo esc_html($last['path']); ?></code></p><?php }
        $seen=[];foreach(self::directories() as $dir) { foreach((array)glob($dir.'/*.csv') as $p) { $name=basename($p);if(isset($seen[$name])) { continue; }$seen[$name]=true;
            ?><p><a class="hpg-btn secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=hpg_log&file='.$name),'hpg_log')); ?>">Download <?php echo esc_html($name); ?></a><small class="hpg-help">Saved in <?php echo esc_html(dirname($p)); ?></small></p><?php
        } }if(!$seen) { echo '<p>No saved CSV archives found. Deletion stops if no archive folder is writable.</p>'; }
    }
    public static function panel() {
        ?><section class="hpg-card hpg-maintenance"><span class="hpg-kicker">DATA & MAINTENANCE</span><h2>History archives and plugin reset</h2><div class="hpg-maintenance-grid"><div class="hpg-archive-box"><h3>CSV history archives</h3><p class="hpg-help">Download saved history and deletion audit records.</p><div class="hpg-archive-scroll"><?php self::archive_links(); ?></div></div><div class="hpg-reset-box"><h3>Fully reset plugin</h3><p>Removes every profile, stored token, SSH key, history entry, setting and scheduled upload.</p><p class="hpg-help">History is archived first. CSV files and GitHub repositories are kept. You will return to GitHub Sync.</p><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Archive history and permanently clear all plugin profiles, credentials, settings and history?');"><input type="hidden" name="action" value="hpg_reset"><?php wp_nonce_field('hpg_reset'); ?><button class="hpg-btn hpg-delete-profile">Archive history & fully reset</button></form></div></div></section><?php
    }
}
