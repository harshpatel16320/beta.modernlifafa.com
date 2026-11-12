<?php
/** Remove schedules on uninstall; preserve user data for reinstall. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
foreach ( array( 'hpg_history_index', 'hpg_history_cleanup', 'hpg_history_cleanup_batch' ) as $hook ) {
    wp_clear_scheduled_hook( $hook );
}
$hpg_profiles = get_posts( array( 'post_type' => 'hpg_sync_profile', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
foreach ( $hpg_profiles as $hpg_profile_id ) {
    wp_clear_scheduled_hook( 'hpg_sync_tick', array( $hpg_profile_id ) );
}
foreach ( get_users( array( 'fields' => 'ID' ) ) as $hpg_user_id ) {
    delete_transient( 'hpg_setup_' . $hpg_user_id );
    delete_transient( 'hpg_error_' . $hpg_user_id );
}
