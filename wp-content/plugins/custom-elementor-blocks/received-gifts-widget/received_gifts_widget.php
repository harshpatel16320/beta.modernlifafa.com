<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Received_Gifts_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'received_gifts';
    }
    public function get_title()
    {
        return __('Received Gifts', 'text-domain');
    }
    public function get_icon()
    {
        return 'eicon-gift';
    }
    public function get_categories()
    {
        return ['general'];
    }
    protected function render()
    {
?>
        <?php
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }
        global $wpdb;
        $current_user_id = get_current_user_id();
        // Table name
        $table_name = $wpdb->prefix . 'purchased_submissions';
        // Fetch rows for current user
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY id DESC",
                $current_user_id
            )
        );
        // Display results
        $wishlist_user = get_current_user_id();
        $meta_key = 'gift_for';
        $meta_key1 = '_gift_for';
        $args = array(
            'limit' => -1,
            'return' => 'ids',
            'status' => ['completed', 'processing', 'confirm', 'transfer']
        );
        $orders = wc_get_orders($args);
        $all_products = [];
        $merged_gifts = [];
        // Add Amazon products from purchased_submissions
        foreach ($results as $row) {
            $amazon_timestamp = 0;
            if (!empty($row->created_at)) {
                $parsed = strtotime($row->created_at);
                $amazon_timestamp = $parsed !== false ? $parsed : 0;
            }
            // Fallback to numeric id if no timestamp available
            if (empty($amazon_timestamp) && !empty($row->id)) {
                $amazon_timestamp = intval($row->id);
            }
            $merged_gifts[] = [
                'type' => 'amazon',
                'product_id' => $row->product_id,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'contact' => $row->contact,
                'amazon_order_id' => $row->amazon_order_id,
                'message' => $row->message,
                'date' => $amazon_timestamp,
                'row_id' => $row->id
            ];
        }
        // Add WooCommerce products from orders
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            foreach ($order->get_items() as $item) {
                $meta_data = $item->get_meta_data();
                if ($item->get_meta($meta_key, true) == $wishlist_user || $item->get_meta($meta_key1, true) == $wishlist_user) {
                    $product = $item->get_product();
                    if ($product) {
                        $order_date = $order->get_date_created();
                        $order_timestamp = $order_date ? $order_date->getTimestamp() : 0;
                        if (empty($order_timestamp) && $order->get_date_modified()) {
                            $order_timestamp = $order->get_date_modified()->getTimestamp();
                        }
                        if (empty($order_timestamp)) {
                            $order_timestamp = intval($order_id);
                        }
                        $merged_gifts[] = [
                            'type' => 'woocommerce',
                            'product' => $product,
                            'item_id' => $item->get_id(),
                            'quantity' => $item->get_quantity(),
                            'status' => $item->get_meta('_gift_status', true) ?: 'pending',
                            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                            'order_id' => $order_id,
                            'gift_note' => $order->get_meta('gift_message'),
                            'custom_price' => $item->get_meta('custom_price', true) ?: '',
                            'deliver_to_couple' => $order->get_meta('deliver_to_couple'),
                            'date' => $order_timestamp,
                            'row_id' => $order_id
                        ];
                    }
                }
            }
        }
        // Sort merged gifts by date (latest first). Use row_id as tiebreaker when dates are equal.
        usort($merged_gifts, function ($a, $b) {
            $dateA = isset($a['date']) ? intval($a['date']) : 0;
            $dateB = isset($b['date']) ? intval($b['date']) : 0;
            if ($dateA === $dateB) {
                $idA = isset($a['row_id']) ? intval($a['row_id']) : 0;
                $idB = isset($b['row_id']) ? intval($b['row_id']) : 0;
                return $idB <=> $idA;
            }
            return $dateB <=> $dateA;
        });
        if (empty($merged_gifts)) { ?>
            <a class="back_wishlist" href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>">Back to
                wishlist</a>
        <?php echo '<p>No received gifts found.</p>';
            return;
        }
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $products_per_page = 10;
        $total_products = count($merged_gifts);
        $total_pages = ceil($total_products / $products_per_page);
        $offset = ($paged - 1) * $products_per_page;
        $paged_gifts = array_slice($merged_gifts, $offset, $products_per_page);
        ?>
        <section class="received-gifts-section 123">
            <div class="container">
                <a class="back_wishlist" href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>">Back to
                    wishlist</a>
                <div class="received-list">
                    <!-- Merged Amazon and WooCommerce gifts start -->
                    <?php
                    foreach ($paged_gifts as $gift_data):
                        $formatted_date = '';
                        if (isset($gift_data['date']) && !empty($gift_data['date'])) {
                            $formatted_date = date('Y-m-d H:i:s', intval($gift_data['date']));
                        }
                        if ($gift_data['type'] === 'amazon'):
                            $product_id = $gift_data['product_id'];
                            // var_dump($product_id);
                            $name = $gift_data['first_name'] . ' ' . $gift_data['last_name'];
                            $contact = $gift_data['contact'];
                            $amazon_order_id = $gift_data['amazon_order_id'];
                            $message = $gift_data['message'];
                            $price = get_field('price', $product_id);
                            $product_name = get_the_title($product_id);
                            $image = mlf_get_registry_product_image($product_id, 'full');
                            $product_category_slugs = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
                            $is_cash_category = in_array('cash', $product_category_slugs);
                    ?>
                            <div class="received-box new-recevice-cls-adding-gift">
                                <div class="received-main">
                                    <div class="received_wrap">
                                        <picture class="received-img">
                                            <img src="<?php echo esc_url($image); ?>" alt="product" width="300" height="300">
                                        </picture>
                                    </div>
                                    <div class="received-content">
                                        <?php if (!empty($name)) { ?>
                                            <h3 class="received-name"><?php echo esc_html($name); ?></h3>
                                        <?php } ?>
                                        <div class="product-name mobile">
                                            <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                                                <?php echo esc_html($product_name); ?>
                                            </a>
                                        </div>
                                        <?php if ($is_cash_category) {
                                            echo "<span class='received_price'>Price: " . $price . '</span>';
                                        } ?>
                                        <p class="purchased-amz">Purchased on <?php
                                                                                $source = get_post_meta($product_id, 'external_platform', true);
                                                                                echo esc_html($source ?: 'External Store');
                                                                                ?></p>
                                        <?php if ($message) { ?>
                                            <span class="gift_message">
                                                <?php echo '<strong>Gift Note : </strong>' . $message; ?>
                                            </span>
                                        <?php } ?>
                                        <div class="received-gift-img">
                                            <div class="product-name desktop">
                                                <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                                                    <?php echo esc_html($product_name); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else:
                            // WooCommerce product
                            $product = $gift_data['product'];
                            $product_id = $product ? $product->get_id() : 0;
                            if ($product && $product->is_type('variation')) {
                                $product_id = $product->get_parent_id();
                            }
                            $product_name = $product->get_name();
                            $price = $product->get_price();
                            if ($gift_data['custom_price'] != '' && !empty($gift_data['custom_price'])) {
                                $price = $gift_data['custom_price'];
                            }
                            $product_category_slugs = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
                            $is_cash_category = in_array('cash', $product_category_slugs);
                            $quantity = $gift_data['quantity'];
                            $product_status = $gift_data['status'];
                            if ($is_cash_category) {
                                $product_status = "Transferred";
                            } else {
                                $product_status = "Shipped";
                            }
                            $customer_name = $gift_data['customer_name'];
                            $gift_message = $gift_data['gift_note'];
                        ?>
                            <div class="received-box new-recevice-cls-adding-all" data-order-id="<?php echo $gift_data['order_id']; ?>"
                                data-item-id="<?php echo $gift_data['item_id']; ?>" data-order-time="<?php echo $formatted_date; ?>"
                                data-status="<?php echo $product_status; ?>">
                                <div class="received-main">
                                    <div class="received_wrap">
                                        <picture class="received-img">
                                            <img src="<?php echo esc_url(wp_get_attachment_url($product->get_image_id()) ?: get_template_directory_uri() . '/assets/images/default_image.png'); ?>"
                                                alt="product" width="300" height="300">
                                        </picture>
                                    </div>
                                    <div class="received-content">
                                        <?php if (!empty($customer_name)) { ?>
                                            <h3 class="received-name"><?php echo esc_html($customer_name); ?></h3>
                                        <?php } ?>
                                        <div class="product-name mobile">
                                            <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                                                <?php echo esc_html($product_name); ?>
                                            </a>
                                        </div>
                                        <?php if ($is_cash_category) {
                                            echo "<span class='received_price'>Price: " . $price . '</span>';
                                        } ?>
                                        <span class="received-qty">Qty: <?php echo esc_html($quantity); ?></span>
                                        <?php if ($gift_message) { ?>
                                            <span class="gift_message">
                                                <?php echo '<strong>Gift Note : </strong>' . $gift_message; ?>
                                            </span>
                                        <?php } ?>
                                        <div class="received-gift-img">
                                            <div class="product-name desktop">
                                                <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                                                    <?php echo esc_html($product_name); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $user_id = get_current_user_id();
                                    $deliver_to_couple = get_user_meta(
                                        $user_id,
                                        'deliver_to_couple',
                                        true
                                    );
                                    /*
                                    * RazorpayX stores the bank connection using the
                                    * Fund Account ID. We intentionally do not store
                                    * the customer's bank account number / IFSC locally.
                                    */
                                    // $fund_account_id = get_user_meta(
                                    //     $user_id,
                                    //     '_ml_rpx_fund_account_id',
                                    //     true
                                    // );
                                    // $has_bank_details = !empty($fund_account_id);
                                    // Check whether the logged-in user has saved a UPI ID.
                                    $upi_id = get_user_meta(
                                        $user_id,
                                        '_ml_upi_id',
                                        true
                                    );
                                    $has_upi_id = !empty(trim($upi_id));
                                    ?>
                                    <div class="received-actions">
                                        <?php if ($gift_data['status'] === 'confirm'):
                                            if ($is_cash_category) { ?>
                                                <button disabled class="custom_button accept-gift secondary_button"
                                                    data-action="accept">Transferred</button>
                                            <?php } else { ?>
                                                <button disabled class="custom_button accept-gift secondary_button"
                                                    data-action="accept">Shipped</button>
                                            <?php }
                                        elseif ($gift_data['status'] === 'expired'): ?>
                                            <button class="custom_button secondary_button" disabled>Expired</button>
                                            <?php else:
                                            if ($is_cash_category) { ?>
                                                <!-- <button class="custom_button accept-gift-transfer secondary_button" data-action="transfer"
                                                 data-user-has-bank="<?php //echo $has_bank_details ? 'true' : 'false'; 
                                                                        ?>">Transfer
                                                                        Now</button> -->
                                                <button type="button" class="custom_button accept-gift-transfer secondary_button"
                                                    data-action="transfer" data-user-has-upi="<?php echo $has_upi_id ? 'true' : 'false'; ?>">
                                                    Transfer Now
                                                </button>
                                            <?php } else { ?>
                                                <?php
                                                $deliver_to_couple = $gift_data['deliver_to_couple'];
                                                if ($deliver_to_couple == 'no') {
                                                    $button_text = 'Shipped to Guest'; ?>
                                                    <button class="custom_button accept-gift secondary_button" disabled
                                                        data-action="shipp"><?php echo $button_text; ?></button>
                                                <?php } else {
                                                    $button_text = 'Ship Now'; ?>
                                                    <button class="custom_button accept-gift secondary_button"
                                                        data-action="shipp"><?php echo $button_text; ?></button>
                                                <?php }
                                                ?>
                                        <?php }
                                        endif; ?>
                                    </div>
                                </div>
                            </div>
                    <?php endif;
                    endforeach; ?>
                </div>
                <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $pagination_args = array(
                    'base' => get_pagenum_link(1) . '%_%',
                    'format' => 'page/%#%',
                    'current' => max(1, $paged),
                    'total' => $total_pages,
                    'prev_next' => false,
                    'prev_text' => __('<span aria-hidden="true" class="fas fa-chevron-left"></span>'),
                    'next_text' => __('<span aria-hidden="true" class="fas fa-chevron-right"></span>'),
                    'type' => 'array',
                );
                $links = paginate_links($pagination_args);
                if ($links) {
                    morden_lifafa_pagination($links, $total_pages, $paged);
                } ?>
            </div>
        </section>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Received_Gifts_Widget());
?>