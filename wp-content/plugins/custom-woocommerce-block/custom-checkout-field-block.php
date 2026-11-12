<?php
/**
 * Plugin Name: Custom Checkout Field for WooCommerce Block
 * Description: Adds a custom field to the WooCommerce block-based checkout page.
 * Version: 1.0
 * Author: Cmarix
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue custom script
function enqueue_custom_checkout_block_script() {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        wp_enqueue_script(
            'custom-checkout-field-block',
            plugin_dir_url(__FILE__) . 'assets/js/custom-checkout-field-block.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wc-blocks-checkout' ), // Ensure dependencies are included
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/custom-checkout-field-block.js'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_checkout_block_script');

// Process and save the custom field
add_action('woocommerce_checkout_create_order', 'save_custom_checkout_field_block', 10, 2);
function save_custom_checkout_field_block($order, $data) {
    if (isset($_POST['custom_field'])) {
        $order->update_meta_data('custom_field', sanitize_text_field($_POST['custom_field']));
    }
}
