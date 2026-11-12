<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Received_Gifts_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'received_gifts';
    }

    public function get_title() {
        return __('Received Gifts', 'text-domain');
    }

    public function get_icon() {
        return 'eicon-gift';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url()); // Redirect to login page
            exit;
        }
        // echo "hgjk";
        $wishlist_user = get_current_user_id();
        $meta_key = 'gift_for';
        $args = array('limit' => -1, 'return' => 'ids', 'status' => ['completed', 'processing']);
        $orders = wc_get_orders($args);
        $matching_orders = [];
        var_dump($orders);
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            foreach ($order->get_items  () as $item) {
                echo $meta_key;
                echo $item->get_meta($meta_key, true);

                if ($item->get_meta($meta_key, true) == $wishlist_user) {
                    $matching_orders[] = $order_id;
                    break;
                }
            }
        }

        if (empty($matching_orders)) {
            echo '<p>No received gifts foundd.</p>';
            return;
        }
        ?>

        <section class="received-gifts-section">
            <div class="container">
                <div class="received-title-wrap">
                    <h1 class="section-title">Received Gifts</h1>
                </div>
                <div class="received-list">
                    <?php foreach ($matching_orders as $order_id):
                        $order = wc_get_order($order_id);
                        foreach ($order->get_items() as $item_id => $item):
                            $product = $item->get_product();
                            $quantity = $item->get_quantity();
                            $product_status = $item->get_meta('_gift_status', true) ?: 'pending';

                            if ($product): ?>
                                <div class="received-box">
                                    <picture class="received-img">
                                        <img src="<?php echo wp_get_attachment_url($product->get_image_id()) ?: get_template_directory_uri() . '/assets/images/default_image.png'; ?>" alt="product-image" width="300" height="300">
                                    </picture>
                                    <div class="received-content">
                                        <h3 class="received-name"><?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></h3>
                                        <span class="received-qty">Qty: <?php echo esc_html($quantity); ?></span>
                                        <!-- <p> -->
                                            <?php //echo esc_html(wp_trim_words($product->get_description(), 15)); ?>
                                        <!-- </p> -->
                                    </div>
                                    <div class="received-actions">
                                        <?php if ($product_status === 'accepted'): ?>
                                            <button class="custom_button secondary_button" disabled>Accepted</button>
                                        <?php elseif ($product_status === 'rejected'): ?>
                                            <button class="custom_button secondary_button" disabled>Rejected</button>
                                        <?php elseif ($product_status === 'expired'): ?>
                                            <button class="custom_button secondary_button" disabled>Expired</button>
                                        <?php else: ?>
                                            <button class="custom_button accept-gift secondary_button" data-action="accept">Accept</button>
                                            <button class="custom_button reject-gift secondary_button" data-action="reject">Reject</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach;
                    endforeach; ?>
                </div>
            </div>
        </section>

        <?php
    }
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Received_Gifts_Widget());
?>
