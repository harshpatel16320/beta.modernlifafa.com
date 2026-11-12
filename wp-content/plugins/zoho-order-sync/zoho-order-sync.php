<?php
/**
 * Plugin Name: Zoho Order Sync
 * Plugin URI: https://cmarix.com
 * Description: Sync WooCommerce orders with Zoho API (Version 1.0.0).
 * Author: cmarix
 * Version: 1.0.0
 * Author URI: https://cmarix.com
 * License: GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include required files
require_once ZOS_PLUGIN_DIR . 'includes/class-zoho-api.php';
require_once ZOS_PLUGIN_DIR . 'includes/class-zoho-order-sync.php';

// Activate and Deactivate hooks
register_activation_hook( __FILE__, array( 'Zoho_Order_Sync', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Zoho_Order_Sync', 'deactivate' ) );

// Initialize the plugin
add_action( 'plugins_loaded', array( 'Zoho_Order_Sync', 'init' ) );
