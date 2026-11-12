<?php
// Calculate discount
function calculate_discount($regular_price, $sale_price)
{
    if ($regular_price > 0 && $regular_price > $sale_price) {
        $price_difference = $regular_price - $sale_price;
        $discount_percentage = ($price_difference / $regular_price) * 100;

        return array(
            'discount_percentage' => $discount_percentage,
            'price_difference' => $price_difference
        );
    } else {
        return array(
            'discount_percentage' => 0,
            'price_difference' => 0
        );
    }
}

function get_matching_variation($product_id, $attributes)
{
    $product = wc_get_product($product_id);
    error_log("custom message.  " . print_r($attributes));
    if (!$product) {
        error_log("Product ID {$product_id} not found.");
        return false;
    }
    if (!$product->is_type('variable')) {
        error_log("Product ID {$product_id} is not a variable product.");
        return false;
    }
    $variations = $product->get_available_variations();
    if (empty($variations)) {
        error_log("No variations found for Product ID {$product_id}. Possible reasons: Variations not published, out of stock, or caching issue.");
        return false;
    }
    error_log("Variations found: " . print_r($variations, true));
    foreach ($variations as $variation) {
        $match = true;
        foreach ($attributes as $name => $value) {
            $attribute_name = 'attribute_' . sanitize_title($name);
            if (!isset($variation['attributes'][$attribute_name]) || sanitize_title($variation['attributes'][$attribute_name]) !== sanitize_title($value)) {
                $match = false;
                break;
            }
        }
        if ($match) {
            error_log("Matching Variation Found: " . $variation['variation_id']);
            return $variation['variation_id'];
        }
    }
    error_log("No matching variation found for Product ID {$product_id}.");
    return false;
}

function sort_wishlist()
{
    $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : '';
    $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : get_current_user_id();
    $received_gift = isset($_POST['received_gift']) ? sanitize_text_field($_POST['received_gift']) : [];
    if (isset($received_gift) && !empty($received_gift)) {
        $received_gift = explode(',', $received_gift);
    }
    // $user_id = get_current_user_id();
    $wishlist_items = get_user_meta($user_id, '_personalized_products', true);
    $wishlist_items = array_column($wishlist_items, 'product_id');
    $product_qty = array();
    $product_qty = get_user_meta($user_id, '_product_quantity', true);
    if ($wishlist_items) {
        usort($wishlist_items, function ($a, $b) use ($sort_order) {
            $price_a = get_post_meta($a, '_price', true);
            $price_b = get_post_meta($b, '_price', true);
            if ($price_a === '' || $price_b === '') {
                return 0;
            }
            if ($sort_order == 'price-asc') {
                return $price_a - $price_b;
            } elseif ($sort_order == 'price-desc') {
                return $price_b - $price_a;
            }
            return 0;
        });
    }
    // var_dump($wishlist_items);
    if ($wishlist_items) {
        personalised_products($wishlist_items, $product_qty, $user_id, $received_gift);
    }
    wp_die();
}
add_action('wp_ajax_sort_wishlist', 'sort_wishlist');
add_action('wp_ajax_nopriv_sort_wishlist', 'sort_wishlist');

function personalised_products($personalized_products, $product_qty, $user_id, $received_item)
{
    //     echo "<pre>";
// var_dump($product_qty);
// echo "<pre>";
    if (!is_array($received_item)) {
        $received_item = explode(',', $received_item); // Convert string to array
    }
    foreach ($personalized_products as $product_data) {
        if (is_array($product_data) && isset($product_data['product_id'])) {
            $product_id = $product_data['product_id']; // Extract product_id from array
        } elseif (is_int($product_data)) {
            $product_id = $product_data; // Use direct integer product ID
        } else {
            continue; // Skip invalid data
        }
        $product = wc_get_product($product_id);
        if ($product && $product->get_status() == 'publish') {
            $thumbnail_id = $product->get_image_id();
            $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : wc_placeholder_img_src();
            $term_names_array = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
            $dis_class = in_array($product_id, $received_item) ? "disable_product" : ""; ?>
            <?php // echo esc_attr($product->get_price()); ?>
            <div class="box presonalised-item <?php echo $dis_class; ?>" data-price="<?php echo esc_attr($product->get_price()); ?>"
                id="box<?php echo $product_id; ?>">
                <div class="product-img">
                    <?php
                    if (!empty($received_item) && in_array($product_id, $received_item)) {
                        $product_url = "javascript:void(0);";
                    } elseif ($user_id == get_current_user_id()) {
                        $product_url = $product->get_permalink();
                    } else {
                        if ($product->is_type('variable')) {
                            $saved_attributes = get_user_meta($user_id, '_product_attributes_' . $product->get_id(), true);
                            $attr = '';
                            if (!empty($saved_attributes)) {
                                error_log("No saved attributes found for user $user_id and product $product_id");
                                if (!empty($saved_attributes['attribute_pa_color'])) {
                                    $attr .= '&attribute_pa_color=' . urlencode($saved_attributes['attribute_pa_color']);
                                }
                                if (!empty($saved_attributes['attribute_pa_size'])) {
                                    $attr .= '&attribute_pa_size=' . urlencode($saved_attributes['attribute_pa_size']);
                                }
                                if (!empty($saved_attributes['attribute_amount'])) {
                                    $attr .= '&attribute_amount=' . urlencode($saved_attributes['attribute_amount']);
                                }
                                if (function_exists('get_matching_variation')) {
                                    $variation = get_matching_variation($product_id, $saved_attributes);
                                    if ($variation && is_a($variation, 'WC_Product_Variation')) {
                                        $price = $variation->get_price();
                                        $product_url = $product->get_permalink() . '?gift_for=' . $user_id . $attr . '&price=' . $price;
                                    } else {
                                        $product_url = $product->get_permalink() . '?gift_for=' . $user_id . $attr;
                                    }
                                } else {
                                    error_log('Function get_matching_variation() is missing.');
                                    $product_url = $product->get_permalink() . '?gift_for=' . $user_id . $attr;
                                }
                            } else {
                                error_log("No saved attributes found for user $user_id and product $product_id");
                                $product_url = $product->get_permalink() . '?gift_for=' . $user_id;
                            }
                        } else {
                            $product_url = $product->get_permalink() . '?gift_for=' . $user_id;
                        }

                    } ?>
                    <a href="<?php echo esc_url($product_url); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="product">
                    </a>
                    <?php if (in_array($product_id, $received_item)) { ?>
                        <div class="product-status-wrapper">
                            <span class="product-status">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                                PURCHASED
                            </span>
                        </div>
                    <?php } ?>
                    <?php if (!empty($received_item)) {
                        if (in_array($product_id, $received_item)) { ?>
                            <div class="tik-mark">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chack-mark.svg" alt="chack-mark"
                                        width="63" height="63">
                                </picture>
                            </div>
                        <?php }
                    } ?>
                </div>
                <div class="product-info">
                    <div class="product-name">
                        <h3 class="product-title"><a
                                href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
                        <p><?php echo wc_price($product->get_price()); ?></p>
                    </div>
                    <div class="product-detail">
                        <p class="product-detail-name"><?php if (isset($term_names_array['0'])) {
                            echo $term_names_array['0'];
                        } ?>
                        </p>
                        <p class="product-quantity">Qty:
                            <span><?php echo ($product_qty[$product_id] >= 1) ? $product_qty[$product_id] : 1; ?></span>
                        </p>
                    </div>
                    <div class="product-title-price">
                        <div class="product-amount">
                            <?php
                            $product_id = $product->get_id();
                            $user_custom_price = get_user_meta($user_id, '_custom_price_' . $product_id, true);
                            $saved_attributes = get_user_meta($user_id, '_product_attributes_' . $product_id, true);
                            $display_price = '';

                            $variation_price = '';
                            if ($product->is_type('variable') && !empty($saved_attributes) && is_array($saved_attributes)) {
                                $available_variations = $product->get_available_variations();

                                foreach ($available_variations as $variation) {
                                    $match = true;
                                    foreach ($saved_attributes as $attribute_name => $attribute_value) {
                                        if (isset($variation['attributes'][$attribute_name])) {
                                            if ($variation['attributes'][$attribute_name] !== $attribute_value) {
                                                $match = false;
                                                break;
                                            }
                                        }
                                    }

                                    if ($match) {
                                        $variation_product = wc_get_product($variation['variation_id']);
                                        if ($variation_product) {
                                            $variation_price = $variation_product->get_price();
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!empty($variation_price)) {
                                $display_price = $variation_price;

                                delete_user_meta($user_id, '_custom_price_' . $product_id);
                            } elseif (!empty($user_custom_price) && $user_custom_price > 0) {
                                $display_price = $user_custom_price;
                            } else {
                                $display_price = $product->get_price();
                            }

                            // if (!empty($display_price) && $display_price > 0) {
                            //     echo get_woocommerce_currency_symbol() . number_format((int)$display_price, 0);
                
                            // }
                            ?>

                            <?php
                            if ($product->is_type('variable') && !empty($saved_attributes)) {
                                if (!empty($saved_attributes['attribute_pa_color'])) {
                                    echo '<p>Selected Color: ' . esc_html($saved_attributes['attribute_pa_color']) . '</p>';
                                }
                                if (!empty($saved_attributes['attribute_pa_size'])) {
                                    echo '<p>Selected Size: ' . esc_html($saved_attributes['attribute_pa_size']) . '</p>';
                                }
                                if (!empty($saved_attributes['attribute_amount'])) {
                                    echo '<p>Selected Amount: ' . esc_html($saved_attributes['attribute_amount']) . '</p>';
                                }
                            }
                            ?>
                        </div>

                        <input type="hidden" class="product_qty" value="<?php echo $product_qty[$product_id]; ?>">
                    </div>
                    <div class="edit-wrap">
                        <?php if ($user_id != get_current_user_id()) { ?>
                            <a href="<?php echo $product_url; ?>">
                                <button type="button" class="custom_button secondary_button button "
                                    data-product_id="  <?php echo esc_attr($product->get_id()); ?>"
                                    data-gift_for="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('View Product', 'woocommerce'); ?></button>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php }
    } ?>
<?php }


// Delete couple account
add_action('wp_ajax_delete_account', 'delete_account');
function delete_account()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You are not logged in.']);
    }
    $user_id = get_current_user_id();
    $password = sanitize_text_field($_POST['password']);
    $user = get_user_by('ID', $user_id);
    if (!wp_check_password($password, $user->user_pass, $user_id)) {
        wp_send_json_error(['message' => 'Incorrect password.']);
    }
    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($user_id);
    wp_send_json_success();
}

// Save custom attributes of cart
function save_selected_attributes_to_user_meta()
{

    $user_id = get_current_user_id();
    $product_id = get_the_ID();

    $attributes = [];

    if (isset($_GET['attribute_pa_color'])) {
        $attributes['color'] = sanitize_text_field($_GET['attribute_pa_color']);
    }
    if (isset($_GET['attribute_pa_size'])) {
        $attributes['size'] = sanitize_text_field($_GET['attribute_pa_size']);
    }
    if (isset($_GET['attribute_amount'])) {
        $attributes['amount'] = sanitize_text_field($_GET['attribute_amount']);
    }

    if (!empty($attributes)) {
        update_user_meta($user_id, '_product_attributes_' . $product_id, $attributes);
    }
}

// Gift message
function display_gift_message_on_cart_page()
{
    ?>
    <div class="gift-message-wrapper">
        <h3><?php _e('Gift Message'); ?></h3>
        <textarea name="gift_message" id="gift_message"
            placeholder="<?php _e('Enter your gift message here'); ?>"></textarea>
        <button type="button" class="button" id="save_gift_message"><?php _e('Save Gift Message'); ?></button>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#save_gift_message').on('click', function () {
                var giftMessage = $('#gift_message').val();
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: {
                        action: 'save_gift_message',
                        gift_message: giftMessage
                    },
                    success: function (response) {
                        alert('Gift message saved!');
                    }
                });
            });
        });
    </script>
    <?php
}

// Save the Gift Message using AJAX
function save_gift_message_to_cart()
{
    if (isset($_POST['gift_message'])) {
        WC()->session->set('gift_message', sanitize_textarea_field($_POST['gift_message']));
        wp_send_json_success();
    }
    wp_die();
}
add_action('wp_ajax_save_gift_message', 'save_gift_message_to_cart');
add_action('wp_ajax_nopriv_save_gift_message', 'save_gift_message_to_cart');

// Add Gift Message to Order Meta during checkout
function add_gift_message_to_order_meta($order_id)
{
    if (WC()->session->get('gift_message')) {
        $gift_message = WC()->session->get('gift_message');
        update_post_meta($order_id, '_gift_message', $gift_message);
    }
}
add_action('woocommerce_checkout_create_order', 'add_gift_message_to_order_meta', 10, 1);

function display_gift_message_in_admin_order($order)
{
    $gift_message = get_post_meta($order->get_id(), '_gift_message', true);
    if (!empty($gift_message)) {
        echo '<p><strong>' . __('Gift Message') . ':</strong> ' . esc_html($gift_message) . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_gift_message_in_admin_order', 10, 1);

// Display Gift Message in the order emails
function display_gift_message_in_email($order, $sent_to_admin, $plain_text, $email)
{
    $gift_message = get_post_meta($order->get_id(), '_gift_message', true);
    if (!empty($gift_message)) {
        echo '<p><strong>' . __('Gift Message') . ':</strong> ' . esc_html($gift_message) . '</p>';
    }
}
add_action('woocommerce_email_customer_details', 'display_gift_message_in_email', 10, 4);

// custom field
add_action(
    'woocommerce_init',
    function () {
        woocommerce_register_additional_checkout_field(
            array(
                'id' => 'namespace/gift-note',
                'label' => 'Gift Note',
                'location' => 'order',
            ),
        );
        add_action(
            'woocommerce_sanitize_additional_field',
            function ($field_value, $field_key) {
                if ('namespace/gift-note' === $field_key || 'namespace/confirm-gift-note' === $field_key) {
                    $field_value = str_replace(' ', '', $field_key);
                    $field_value = strtoupper($field_value);
                }
                return $field_value;
            },
            10,
            2
        );

        add_action(
            'woocommerce_validate_additional_field',
            function (WP_Error $errors, $field_key, $field_value) {
                if ('namespace/gift-note' === $field_key) {
                    $match = preg_match('/[A-Z0-9]{5}/', $field_value);
                    if (0 === $match || false === $match) {
                        $errors->add('invalid_gov_id', 'Please ensure your Gift Note matches the correct format.');
                    }
                }
                return $error;
            },
            10,
            3
        );
    }
);

function check_gift_recipient_in_cart($passed, $product_id, $quantity)
{
    $gift_for = isset($_POST['gift_for']) ? sanitize_text_field($_POST['gift_for']) : '';
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (isset($cart_item['gift_for'])) {
            if ($cart_item['gift_for'] !== $gift_for) {
                wc_add_notice(__('You can only add products for the same recipient in the cart.'), 'error'); ?>
                <script type="text/javascript">
                    setTimeout(function () {
                        toastr.warning('You can only add products for the same recipient in the cart.');
                    }, 2000);
                </script>
                <?php return false;
            }
        }
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'check_gift_recipient_in_cart', 10, 3);

add_action('wp_footer', 'display_account_update_alert');

function display_account_update_alert()
{
    if (isset($_COOKIE['account_update_alert'])) {
        // if (!is_admin()) {
        //     wp_redirect(home_url('/wishlist/'));
        //     exit;
        // }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                toastr.options = {
                    'closeButton': true,
                    'debug': false,
                    'newestOnTop': false,
                    'progressBar': true,
                    'positionClass': 'toast-top-right',
                    'preventDuplicates': false,
                    'showDuration': '1000',
                    'hideDuration': '1000',
                    'timeOut': '5000',
                    'extendedTimeOut': '1000',
                    'showEasing': 'swing',
                    'hideEasing': 'linear',
                    'showMethod': 'fadeIn',
                    'hideMethod': 'fadeOut',
                };
                setTimeout(() => toastr.success('Details updated successfully!'), 800);
            });
        </script>
        <?php
        // Unset the cookie to prevent repeated alerts.
        wc_setcookie('account_update_alert', '', time() - 3600);
    }
}

// add_action('woocommerce_customer_save_account_details', function($user_id) {
//     if (!is_admin()) {
//         wp_redirect(home_url('//')); // Change to your desired URL
//         exit;
//     }
// });


// Handle AJAX request to add product to cart
function custom_ajax_add_to_cart()
{
    if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $added = WC()->cart->add_to_cart($product_id);
        if ($added) {
            wp_send_json_success(array(
                'message' => 'Product added to cart',
                'cart_url' => wc_get_cart_url(),
                'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
            ));
        } else {
            wp_send_json_error(array('error' => 'Could not add product to cart.'));
        }
    }
    wp_die();
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'custom_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'custom_ajax_add_to_cart');

add_action('wp_ajax_save_custom_price', 'save_custom_price');
add_action('wp_ajax_nopriv_save_custom_price', 'save_custom_price');

function save_custom_price()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in'));
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $custom_price = isset($_POST['custom_price']) ? floatval($_POST['custom_price']) : 0;
    $user_id = get_current_user_id();

    if ($product_id && $custom_price > 0) {
        update_user_meta($user_id, '_custom_price_' . $product_id, $custom_price);
        wp_send_json_success(array('message' => 'Custom price saved.'));
    } else {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }
}
add_action('wp_ajax_save_custom_price', 'save_custom_price');

// Additional charges on cash category

add_action( 'woocommerce_cart_calculate_fees', function () {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    $category_slug = 'cash';
    $percentage    = 5;
    $extra_charge  = 0;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( has_term( $category_slug, 'product_cat', $cart_item['product_id'] ) ) {
            $product_price = $cart_item['data']->get_price();
            $quantity      = $cart_item['quantity'];
            $extra_charge += ( $product_price * $percentage / 100 ) * $quantity;
        }
    }
    if ( $extra_charge > 0 ) {
        WC()->cart->add_fee( 'Platform Fees (' . $percentage . '%)', $extra_charge, true, 'standard' );
    }
});


function get_custom_related_products($product_id, $posts_per_page = -1)
{
    $product = wc_get_product($product_id);
    $categories = $product->get_category_ids();
    $tags = $product->get_tag_ids();
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post__not_in' => [$product_id],
        'tax_query' => [
            'relation' => 'OR',
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $categories,
            ],
            [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => $tags,
            ],
        ],
    ];
    $related_products = new WP_Query($args);
    if ($related_products->have_posts()) { ?>
        <section class="product-listing-section similar-listing-section">
            <div class="container">
                <h3 class="similar-title">Similar Products</h3>
                <div class="swiper similarSwiper">
                    <div class="swiper-wrapper product-list">
                        <?php while ($related_products->have_posts()) {
                            $related_products->the_post();
                            global $product;
                            $discount = '';
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                            if ($sale_price) {
                                $discount = calculate_discount($regular_price, $sale_price);
                                $discount = round($discount['discount_percentage'], 2) . '%';
                            } ?>
                            <div class="swiper-slide product-box">
                                <div class="product-img">
                                    <a href="<?php echo get_permalink($product->get_id()); ?>">
                                        <picture>
                                            <img class="lazyload"
                                                data-src="<?php echo wp_get_attachment_url($product->get_image_id()); ?>"
                                                src="<?php echo wp_get_attachment_url($product->get_image_id()); ?>"
                                                alt="best-sellers" width="288" height="212">
                                        </picture>
                                    </a>
                                    <button class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#quickview-modal"><span
                                            class="icon-eye-open"></span></button>
                                </div>
                                <div class="product-content">
                                    <div class="product-title-price">
                                        <div class="name-head">
                                            <h3 class="product-title">
                                                <a href="<?php echo get_permalink($product->get_id()); ?>"><?php echo $product->get_title(); ?></a>
                                            </h3>
                                            <p>Turkey</p>
                                        </div>
                                        <div class="product-amout">
                                            <?php echo get_woocommerce_currency_symbol() . $product->get_price(); ?>
                                        </div>
                                    </div>
                                    <p><?php echo mb_substr(wp_strip_all_tags($product->get_description()), 0, 80) . '...'; ?></p>
                                    <div class="product-sku"><?php echo $product->get_sku(); ?></div>
                                    <button class="custom_button add-to-personalized"
                                        data-product-id="<?php echo $product->get_id(); ?>" data-action="add">Add to
                                        Wishlist</button>
                                </div>
                            </div>
                        <?php }
                        wp_reset_postdata(); ?>
                    </div>
                </div>
                <hr class="line">
            </div>
        </section>
    <?php }
}

add_action('woocommerce_single_product_summary', 'display_custom_product_variations', 25);
function display_custom_product_variations() {
    global $product;
    if ($product->is_type('variable')) {
        $available_variations = $product->get_available_variations();
        if (!empty($available_variations)) {
            echo '<div class="custom-variations">';
            foreach ($available_variations as $variation) {
                $variation_attributes = $variation['attributes'];
                $price = wc_price($variation['display_price']);
                $amount = $variation_attributes['attribute_pa_amount']; // Adjust if your attribute slug is different

                echo "<div class='variation-item'>";
                echo "<span class='variation-amount'>Amount: $amount</span>";
                echo "<span class='variation-price'>Price: $price</span>";
                echo "</div>";
            }
            echo '</div>';
        }
    }
}

add_action('wp', 'save_selected_attributes_to_user_meta');

// 24-03-2025
// add_filter( 'woocommerce_billing_fields', function( $fields ) {
//     if ( isset( $fields['billing_address_1'] ) ) {
//         $fields['billing_address_1']['required'] = false;
//     }
//     if ( isset( $fields['billing_address_2'] ) ) {
//         $fields['billing_address_2']['required'] = false;
//     }
//     if ( isset( $fields['billing_city'] ) ) {
//         $fields['billing_city']['required'] = false;
//     }
//     if ( isset( $fields['billing_postcode'] ) ) {
//         $fields['billing_postcode']['required'] = false;
//     }
//     if ( isset( $fields['billing_state'] ) ) {
//         $fields['billing_state']['required'] = false;
//     }
//     if ( isset( $fields['billing_country'] ) ) {
//         $fields['billing_country']['required'] = false;
//     }
//     return $fields;
// }, 20 );

add_filter( 'woocommerce_shipping_fields', function( $fields ) {
    foreach ( $fields as $key => $field ) {
        $fields[$key]['required'] = false;
    }
    return $fields;
}, 20 );


// Checkout 24-03-25
add_action('woocommerce_before_checkout_form', function() {
    ?>
    <div class="delever_to_couple_wrap">
        <input type="checkbox" id="deliver_to_couple" name="deliver_to_couple" checked>
        <label for="deliver_to_couple">Deliver to the Couple</label>
</div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let checkbox = document.getElementById("deliver_to_couple");
        let shippingFields = document.querySelector(".shipping_address");

        function toggleShipping() {
            if (checkbox.checked) {
                // alert('called');
                shippingFields.style.display = "none";
            } else {
                // alert('called2');
                shippingFields.style.display = "block";
            }
        }

        checkbox.addEventListener("change", toggleShipping);
        toggleShipping(); // Run on load
    });
    </script>
    <?php
}, 5);


add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    if (isset($_POST['deliver_to_couple'])) {
        $couple_address = [
            'first_name' => 'Couple',
            'last_name'  => 'Recipient',
            'address_1'  => '123 Wedding Street',
            'city'       => 'City',
            'state'      => 'State',
            'postcode'   => '12345',
            'country'    => 'US',
        ];

        // Set shipping address to the couple’s address
        $order = wc_get_order($order_id);
        $order->set_address($couple_address, 'shipping');
    }
});
