<?php
/**
 * Plugin Name: Custom Elementor Widgets
 * Description: Custom Elementor widgets with structured folders.
 * Version: 1.0
 * Author: Cmarix
 */

if (!defined('ABSPATH')) exit;

function register_custom_elementor_widgets() {
    require_once plugin_dir_path(__FILE__) . 'best-selling-products/best-selling-products.php';
    require_once plugin_dir_path(__FILE__) . 'couple-wishlist-search-widget/couple-wishlist-search-widget.php';
    require_once plugin_dir_path(__FILE__) . 'wishlist-widget/wishlist-widget.php';
    require_once plugin_dir_path(__FILE__) . 'wishlist-share-widget/wishlist-share-widget.php';
    require_once plugin_dir_path(__FILE__) . 'brand-listing-widget/brand-listing-widget.php';
    require_once plugin_dir_path(__FILE__) . 'product-listing-widget/product-listing-widget.php';
    require_once plugin_dir_path(__FILE__) . 'received-gifts-widget/received_gifts_widget.php';
    require_once plugin_dir_path(__FILE__) . 'product-gallery/widget-variation-image.php';
}
add_action('elementor/widgets/register', 'register_custom_elementor_widgets');

function custom_elementor_widgets_scripts() {
    wp_enqueue_style('best-selling-products-style', plugin_dir_url(__FILE__) . 'best-selling-products/best-selling-products.css');
}
add_action('wp_enqueue_scripts', 'custom_elementor_widgets_scripts');
