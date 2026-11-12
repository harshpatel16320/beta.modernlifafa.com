<?php

use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit;

class Widget_Variation_Image extends Widget_Base {

    public function get_name() {
        return 'variation_image_widget';
    }

    public function get_title() {
        return 'Variation Image (Woo)';
    }

    public function get_icon() {
        return 'eicon-product-images';
    }

    public function get_categories() {
        return ['woocommerce-elements'];
    }

    public function render() {
        global $product;

        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        if (!$product) return;

        echo '<div class="custom-variation-widget">';
        
        // Gallery section
        echo '<div class="woocommerce-product-gallery">';
        do_action('woocommerce_before_single_product_summary'); // This includes the image gallery
        echo '</div>';

        // Variation dropdowns & add to cart
        if ($product->is_type('variable')) {
            echo '<div class="woocommerce-variation-form">';
            woocommerce_variable_add_to_cart(); // Includes the variation dropdowns
            echo '</div>';
        }

        echo '</div>';
    }
}

// Register widget
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widget_Variation_Image());

