<?php
if(!defined('ABSPATH')) { exit; }
final class HPG_Access {
    public static function allowed($operation,$user=null) {
        if($user===null && current_user_can('manage_options')) { return true; }
        $user=$user?:wp_get_current_user();if(!$user || !$user->ID) { return false; }
        if(user_can($user,'manage_options')) { return true; }
        $p=get_option('hpg_permissions',[]);$role=$p[$operation.'_role']??'administrator';
        if($operation==='merge' && empty($p['delegate_merge'])) { return false; }
        return $role==='authenticated' || in_array($role,(array)$user->roles,true);
    }
    public static function admin() { return current_user_can('manage_options'); }
    public static function capability($caps,$cap,$id,$args) {
        if($cap==='hpg_push' || $cap==='hpg_tools') {
            $user=get_userdata($id);$allowed=self::allowed('push',$user) || ($cap==='hpg_tools' && self::allowed('merge',$user));return $allowed?['read']:['do_not_allow'];
        }return $caps;
    }
    public static function check($task,$config) {
        $admin=self::admin();
        if(in_array($task,['push','preview'],true)) {
            if(!self::allowed('push')) { throw new RuntimeException('Your role is not allowed to push this repository.'); }
            $branch=!empty($config['date_branch'])?wp_date('Y-m-d'):$config['branch'];
            if(!$admin && ($branch==='main' || (!empty($config['date_branch']) && $config['branch']==='main'))) { throw new RuntimeException('Only administrators may push or pull the main branch.'); }
        } elseif(in_array($task,['create_pr','accept_pr','merge_branches'],true)) {
            if(!self::allowed('merge')) { throw new RuntimeException('Merge tools are disabled for your role. An administrator must enable them.'); }
        } elseif(in_array($task,['compare','review_pr','find_pr','branches'],true)) {
            if(!self::allowed('push') && !self::allowed('merge')) { throw new RuntimeException('Git review access denied.'); }
        } elseif(!$admin) { throw new RuntimeException('Profile configuration, credentials and fetching require administrator access.'); }
    }
    public static function protect_main($branches) {
        if(!self::admin() && in_array('main',$branches,true)) { throw new RuntimeException('Only administrators may pull or merge with the main branch.'); }
    }
}
