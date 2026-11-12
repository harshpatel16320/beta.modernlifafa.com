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
    $current_user = get_current_user_id();
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

// function sort_wishlist()
// {
//     $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : '';
//     $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : get_current_user_id();
//     $received_gift = isset($_POST['received_gift']) ? sanitize_text_field($_POST['received_gift']) : [];
//     if (isset($received_gift) && !empty($received_gift)) {
//         $received_gift = explode(',', $received_gift);
//     }
//     // $user_id = get_current_user_id();
//     $wishlist_items = get_user_meta($user_id, '_personalized_products', true);
//     $wishlist_items = array_column($wishlist_items, 'product_id');
//     $product_qty = array();
//     $product_qty = get_user_meta($user_id, '_product_quantity', true);
//     if ($wishlist_items) {
//         usort($wishlist_items, function ($a, $b) use ($sort_order) {
//             $price_a = get_post_meta($a, '_price', true);
//             $price_b = get_post_meta($b, '_price', true);
//             if ($price_a === '' || $price_b === '') {
//                 return 0;
//             }
//             if ($sort_order == 'price-asc') {
//                 return $price_a - $price_b;
//             } elseif ($sort_order == 'price-desc') {
//                 return $price_b - $price_a;
//             }
//             return 0;
//         });
//     }
//     // var_dump($wishlist_items);
//     if ($wishlist_items) {
//         personalised_products($wishlist_items, $product_qty, $user_id, $received_gift);
//     }
//     wp_die();
// }
// add_action('wp_ajax_sort_wishlist', 'sort_wishlist');
// add_action('wp_ajax_nopriv_sort_wishlist', 'sort_wishlist');

function personalised_products($personalized_products, $product_qty, $user_id, $received_item1, $gift_for)
{
    $current_user_id = get_current_user_id();
    $original_user_id = isset($gift_for) ? intval($gift_for) : '';
    // if (!is_array($received_item)) {
    //     $received_item = explode(',', $received_item); 
    // }

    $received_item = [];
    $total_amount = 0;

    // Fetch Orders Where User Has Received Gifts
    $orders = wc_get_orders([
        'limit' => -1,
        'status' => ['completed', 'processing'],
        'return' => 'ids',
    ]);
    $received_item_attributes = [];
    if (!empty($orders)) {
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            foreach ($order->get_items() as $item) {
                if ($item->get_meta('_gift_for', true) == $user_id || $item->get_meta('gift_for', true) == $user_id) {
                    $product = $item->get_product();
                    if ($product) {
                        $product_id = $product->get_id();
                        $variation_id = $item->get_variation_id();
                        if (has_term('cash', 'product_cat', $product->get_id())) {
                            $total_amount += $item->get_total();
                        }
                        $received_item[] = $product->get_id();
                    }
                    if ($product && $product->is_type('variation')) {
                        $variation_attributes = $product->get_variation_attributes();
                        $received_item_attributes[] = [
                            'product_id' => $product_id,
                            'variation_id' => $variation_id,
                            'attributes' => $variation_attributes,
                        ];
                    }
                }
            }
        }
    }

    // $personalized_products_a = get_user_meta($user_id, '_personalized_products', true) ?? [];

    // if (!is_array($personalized_products_a)) {
    //     $personalized_products_a = explode(',', $personalized_products_a);
    // }
    // $filtered_products = array_intersect($personalized_products_a, array_column($personalized_products, 'product_id'));
    if ($personalized_products) {
        foreach ($personalized_products as $product_data) {
            if (!is_array($product_data) || !isset($product_data['product_id']))
                continue;
            $variation_image_url = null;
            $product_id = $product_data['product_id'];

            if (isset($product_data['source']) && $product_data['source'] === 'amazon') {
                display_amazon_product($product_data, $original_user_id);
                continue;
            }

            $attributes = isset($product_data['attributes']) ? $product_data['attributes'] : [];
            $unique_key = $product_id . '_' . md5(json_encode($attributes));

            $product = wc_get_product($product_id);
            if (!$product || $product->get_status() !== 'publish')
                continue;

            $product_qty_all = get_user_meta($user_id, '_product_quantity', true);
            $saved_attributes = $attributes;
            $variation_key = $product_id . '_' . md5(json_encode($saved_attributes));
            $qty = isset($product_qty_all[$variation_key]) ? $product_qty_all[$variation_key] : $product_data['quantity'];

            $term_names_array = wp_get_post_terms($product_id, 'brand', ['fields' => 'names']);
            $current_user_id = get_current_user_id();
            // $original_user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : 0;
            $user_custom_price = get_user_meta($user_id, '_custom_price_' . $product_id, true);
            $display_price = '';
            $product_url = $product->get_permalink();

            if (isset($original_user_id)) {
                $product_url = add_query_arg('gift_for', $user_id, $product_url);
            }

            // Append attributes to URL
            foreach ($saved_attributes as $attr_key => $attr_val) {
                if (!empty($attr_val)) {
                    $product_url = add_query_arg($attr_key, urlencode($attr_val), $product_url);
                }
            }

            // Add custom price to URL (separately, not by overwriting attr key)
            if (!empty($user_custom_price)) {
                $product_url = add_query_arg('custom_price', urlencode($user_custom_price), $product_url);
            }

            $product_url = add_query_arg('quantity', intval($qty), $product_url);

            $variation_price = '';
            $matched_variation_id = 0;
            if ($product->is_type('variable')) {
                $available_variations = $product->get_available_variations();
                foreach ($available_variations as $variation) {
                    $match = true;
                    foreach ($saved_attributes as $attribute_name => $attribute_value) {
                        if (!empty($variation['attributes'][$attribute_name]) && $variation['attributes'][$attribute_name] !== $attribute_value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        // var_dump($product_data);
                        $saved_variation_id = $product_data['variation_id'];
                        if ($saved_variation_id != '' && isset($saved_variation_id) && !empty($saved_variation_id)) {
                            $variation_product = wc_get_product($saved_variation_id);
                        } else {
                            $variation_product = wc_get_product($variation['variation_id']);
                        }
                        if ($variation_product) {
                            $variation_price = $variation_product->get_price();
                            $matched_variation_id = $variation['variation_id'];

                            // Get the variation image
                            $variation_image_id = $variation_product->get_image_id();
                            if ($variation_image_id) {
                                $variation_image_url = wp_get_attachment_image_url($variation_image_id, 'full');
                            } else {
                                $default_image_id = $product->get_image_id();
                                $variation_image_url = $default_image_id ? wp_get_attachment_image_url($default_image_id, 'full') : wc_placeholder_img_src();
                            }

                            foreach ($variation['attributes'] as $key => $value) {
                                if (!empty($value)) {
                                    $product_url = add_query_arg($key, urlencode($value), $product_url);
                                }
                            }
                            break;
                        }
                    } else {
                        foreach ($saved_attributes as $attr_key => $attr_val) {
                            if (!empty($attr_val)) {
                                $product_url = add_query_arg('attribute_' . sanitize_title($attr_key), urlencode($attr_val), $product_url);
                            }
                        }
                    }
                }
            }

            // Determine price
            if (!empty($variation_price)) {
                $display_price = $variation_price;
            } elseif (!empty($user_custom_price)) {
                $display_price = $user_custom_price;
            } else {
                $display_price = $product->get_price();
            }

            $thumbnail_id = $product->get_image_id();
            $image_url = $variation_image_url ?? ($thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : wc_placeholder_img_src());

            $children_ids = $product->is_type('variable') ? $product->get_children() : [];
            $check_ids = $product->is_type('variation') ? [$product_id, $product->get_parent_id()] : [$product_id];
            $check_ids = array_merge($check_ids, $children_ids);
            $dis_class = ""; // Default
            if (!has_term('cash', 'product_cat', $product->get_id()) && array_intersect($check_ids, $received_item)) {
                if ($product->is_type('variable')) {
                    $purchased = false;
                    foreach ($received_item_attributes as $item) {
                        if ($item['variation_id'] == $matched_variation_id) {
                            $purchased = true;
                            break;
                        }
                    }
                    if ($purchased) {
                        $dis_class = "disable_product";
                    }
                } else {
                    $dis_class = "disable_product";
                }
            }
            $is_out_of_stock = !$product->is_in_stock();
            ?>
            <div class="box presonalised-item <?php echo esc_attr($dis_class); ?> <?php echo $is_out_of_stock ? 'product_disabled' : ''; ?>"
                data-price="<?php echo esc_attr($display_price); ?>">
                <div class="product-img ajax-filters">
                    <a href="<?php echo esc_url($product_url); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="product">
                    </a>

                    <?php if (array_intersect($check_ids, $received_item) && !has_term('cash', 'product_cat', $product->get_id())):
                        if ($product->is_type('variable')) {
                            $purchased = false;
                            foreach ($received_item_attributes as $item) {
                                if ($item['variation_id'] == $matched_variation_id) {
                                    $purchased = true;
                                    break;
                                }
                            }
                            if ($purchased) { ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                                        PURCHASED
                                    </span>
                                </div>
                            <?php }
                        } else { ?>
                            <div class="product-status-wrapper">
                                <span class="product-status">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                                    PURCHASED
                                </span>
                            </div>
                        <?php }
                    elseif ($is_out_of_stock): ?>
                        <div class="product-status-wrapper">
                            <span class="product-status">OUT OF STOCK</span>
                        </div>
                    <?php endif; ?>
                    <?php if (is_user_logged_in() && !array_intersect($check_ids, $received_item) && (!$original_user_id)): ?>
                        <button class="custom_button remove_product" data-product_id="<?php echo esc_attr($product_id); ?>">
                            <i aria-hidden="true" class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <div class="product-name">
                        <h3 class="product-title">
                            <?php if (array_intersect($check_ids, $received_item)) {
                                $product_url = "javascript:void(0)";
                            } ?>
                            <a href="<?php echo esc_url($product_url); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </a>
                        </h3>
                        <?php
                        if (has_term('cash', 'product_cat', $product->get_id())) {
                            echo '<p></p>';
                        }
                        ?>
                        <?php
                        if (!has_term('cash', 'product_cat', $product->get_id())) {
                            echo '<p>' . wc_price($display_price) . '</p>';
                        }
                        ?>
                        <input type="hidden" name="variation_id" value="<?php echo esc_attr($matched_variation_id); ?>" />

                    </div>

                    <div class="product-detail">
                        <p class="product-detail-name">
                            <?php echo isset($term_names_array[0]) ? esc_html($term_names_array[0]) : ''; ?>
                        </p>
                        <?php if ($qty == 0 || $qty == '' || empty($qty)) {
                            $qty = 1;
                        } ?>
                        <?php
                        if ($product->is_type('variable')) {
                            $qty_class = 'updt_qty_' . $matched_variation_id;
                        } else {
                            $qty_class = 'updt_qty_' . $product->get_id();
                        } ?>
                        <?php
                        // if ((is_user_logged_in() && !array_intersect($check_ids, $received_item) && !has_term('cash', 'product_cat', $product->get_id())) && !$is_out_of_stock ){
                        // }else
                        if (!has_term('cash', 'product_cat', $product->get_id())) { ?>
                            <p class="product-quantity">Qty: <span class="<?php echo $qty_class; ?>">
                                    <?php echo esc_html($qty); ?>
                                </span></p>
                        <?php } ?>
                    </div>
                    <?php
                    if (!empty($user_custom_price)) {
                    } else {
                        if (!empty($saved_attributes) && !has_term('cash', 'product_cat', $product->get_id())) { ?>
                            <div class="product-category-details-parent">
                                <div class="product-detail product-category-details">
                                    <?php
                                    foreach ($saved_attributes as $attribute_key => $attribute_value) {
                                        if (!empty($attribute_value)) {
                                            $attribute_name = wc_attribute_label(str_replace('attribute_', '', $attribute_key), $product);
                                            $taxonomy = str_replace('attribute_', '', $attribute_key);

                                            if (taxonomy_exists($taxonomy)) {
                                                // If this is a global attribute with term values
                                                $term = get_term_by('slug', $attribute_value, $taxonomy);

                                                if ($term && !is_wp_error($term)) {
                                                    $formatted_value = $term->name; // This gives 'Green', 'XXL', etc.
                                                } else {
                                                    $formatted_value = ucwords(str_replace(['-', '_'], ' ', $attribute_value));
                                                }
                                            } else {
                                                // For custom attributes (not taxonomy based)
                                                $formatted_value = ucwords(str_replace(['-', '_'], ' ', $attribute_value));
                                            }

                                            echo '<p><strong>' . esc_html($attribute_name) . '</strong>: ' . esc_html($formatted_value) . '</p>';
                                        }
                                    }
                                    ?>

                                    <?php
                                    // var_dump($saved_attributes);
                                    // foreach ($saved_attributes as $attribute_key => $attribute_value) {
                                    //     if (!empty($attribute_value)) {
                                    //         $attribute_name = wc_attribute_label(str_replace('attribute_', '', $attribute_key), $product);
                                    //         echo '<p><strong>' . esc_html($attribute_name) . '</strong>: ' . esc_html(ucwords(str_replace(['-', '_'], ' ', strtolower(wc_attribute_label($attribute_value))))) . '</p>';
                                    //     }
                                    // }
                                    ?>
                                </div>
                                <div class="product-detail product-category-details detail-tooltip">
                                    <?php
                                    foreach ($saved_attributes as $attribute_key => $attribute_value) {
                                        if (!empty($attribute_value)) {
                                            $attribute_name = wc_attribute_label(str_replace('attribute_', '', $attribute_key), $product);
                                            echo '<p><strong>' . esc_html($attribute_name) . '</strong>: ' . esc_html(ucwords(str_replace(['-', '_'], ' ', strtolower(wc_attribute_label($attribute_value))))) . '</p>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php }
                    } ?>
                    <input type="hidden" name="variation_attributes_<?php echo esc_attr($unique_key); ?>"
                        value='<?php echo esc_attr(json_encode($saved_attributes)); ?>'>
                    <?php if ((isset($original_user_id) && !empty($original_user_id) && $current_user_id != $original_user_id && has_term('cash', 'product_cat', $product->get_id()))) { ?>
                        <div class="lifafa_amount new-lifafaamount-cls-adding-here">
                            <label>Contribute what you wish </label>
                            <div class="input-with-rupee">
                                <span class="rupee-symbol">₹</span>
                                <input type="number" class="custom_amount_<?php echo esc_attr($unique_key); ?>" min="1" value=""
                                    placeholder="Enter Gift amount" oninput="toggleRupeeSymbol(this)">
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    if (
                        isset($original_user_id) && !empty($original_user_id) &&
                        $current_user_id != $original_user_id
                    ) {
                        $is_cash_product = has_term('cash', 'product_cat', $product->get_id());
                        $is_in_received_item = array_intersect($check_ids, $received_item);
                        $show_add_to_cart = false;
                        $show_purchased = false;

                        // CASE 1: Show Add to Cart if it's a cash product or not yet received
                        if ($is_cash_product || !$is_in_received_item) {
                            $show_add_to_cart = true;
                        }

                        // CASE 2: If it's a variable product, check if the variation is purchased
                        elseif ($product->is_type('variable') && $is_in_received_item) {
                            $purchased = false;
                            foreach ($received_item_attributes as $item) {
                                if ($item['variation_id'] == $matched_variation_id) {
                                    $purchased = true;
                                    break;
                                }
                            }

                            if ($purchased) {
                                $show_purchased = true;
                            } else {
                                $show_add_to_cart = true;
                            }
                        }

                        // CASE 3: If it's a simple product and received
                        elseif (!$product->is_type('variable') && $is_in_received_item) {
                            $show_purchased = true;
                        }

                        // Output: Add to Cart Button
                        if ($show_add_to_cart): ?>
                            <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                            <input type="hidden" name="attributes" id="hidden_attributes_<?php echo esc_attr($unique_key); ?>"
                                value="<?php echo esc_attr(json_encode($saved_attributes)); ?>" />
                            <input type="hidden" name="quantity" id="hidden_quantity_<?php echo esc_attr($unique_key); ?>"
                                value="<?php echo esc_attr($qty); ?>" />
                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>" class="custom_button <?php if ($is_cash_product)
                                   echo "is_cash_product"; ?> add_to_cart_from_wishlist" data-key="<?php echo esc_attr($unique_key); ?>">
                                Add to Cart
                            </button>
                            <?php
                        endif;
                    }
                    ?>
                    <?php
                    $purchased = false;
                    if (array_intersect($check_ids, $received_item) && !has_term('cash', 'product_cat', $product->get_id())) {
                        // Check if variable product variation was purchased
                        if ($product->is_type('variable')) {
                            foreach ($received_item_attributes as $item) {
                                if ($item['variation_id'] == $matched_variation_id) {
                                    $purchased = true;
                                    break;
                                }
                            }
                        } else {
                            // Simple product in received list = purchased
                            $purchased = true;
                        }
                    }
                    if (
                        (!isset($original_user_id) || empty($original_user_id) || $current_user_id == $original_user_id)
                        && !has_term('cash', 'product_cat', $product->get_id())
                        && !$is_out_of_stock
                        && !$purchased // <- Prevents showing quantity if already purchased
                    ): ?>
                        <div class="product-quantity">
                            <?php
                            if ($product->is_type('variable')) {
                                $qty_class = 'updt_qty_' . $matched_variation_id;
                            } else {
                                $qty_class = 'updt_qty_' . $product->get_id();
                            } ?>
                            <span class="update_wishlist_qty" id="<?php echo $qty_class; ?>">Update Qty</span>
                            <div class="qty-wrap">
                                <?php
                                $stock_quantity = $product->get_stock_quantity();
                                $max_qty = $product->managing_stock() && !$product->is_on_backorder() ? $stock_quantity : '';
                                ?>
                                <button type="button" class="qty-minus">−</button>
                                <input type="number" data-attributes="<?php echo esc_attr(json_encode($saved_attributes)); ?>"
                                    class="personalized-product-qty" data-product_id="<?php echo esc_attr($product_id); ?>"
                                    data-variation_id="<?php echo isset($matched_variation_id) ? esc_attr($matched_variation_id) : ''; ?>"
                                    value="<?php echo esc_attr($qty); ?>" min="1" <?php if ($max_qty !== '')
                                           echo 'max="' . esc_attr($max_qty) . '"'; ?>>
                                <button type="button" class="qty-plus">+</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }

?>
<?php }


// Delete couple account
add_action('wp_ajax_delete_account', 'delete_account');

function get_product_attributes_from_personalized_data($personalized_products_a, $target_product_id)
{
    foreach ($personalized_products_a as $item) {
        if (
            is_array($item)
            && isset($item['product_id'])
            && (int) $item['product_id'] === (int) $target_product_id
        ) {
            return isset($item['attributes']) ? $item['attributes'] : [];
        }
    }
    return [];
}
function get_product_quantity_from_personalized_data($personalized_products_a, $target_product_id)
{
    foreach ($personalized_products_a as $item) {
        if (is_array($item) && isset($item['product_id']) && (int) $item['product_id'] === (int) $target_product_id) {
            return isset($item['quantity']) && $item['quantity'] >= 1 ? (int) $item['quantity'] : 1;
        }
    }
    return 1;
}
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
        <h3>
            <?php _e('Gift Message'); ?>
        </h3>
        <textarea name="gift_message" id="gift_message"
            placeholder="<?php _e('Enter your gift message here'); ?>"></textarea>
        <button type="button" class="button" id="save_gift_message">
            <?php _e('Save Gift Message'); ?>
        </button>
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
    if ($gift_for == '') {
        $gift_for = isset($_POST['_gift_for']) ? sanitize_text_field($_POST['_gift_for']) : '';
    }
    if ($gift_for == '') {
        $gift_for = isset($_GET['original_user_id']) ? sanitize_text_field($_GET['original_user_id']) : '';
    }
    if ($gift_for == '') {
        $gift_for = isset($_GET['gift_for']) ? sanitize_text_field($_GET['gift_for']) : '';
    }
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
// add_action('wp_ajax_save_custom_price', 'save_custom_price');

// Additional charges on cash category

add_action('woocommerce_cart_calculate_fees', function () {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $category_slug = 'cash';
    $percentage = 2.5; // extra charge
    $extra_charge = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (has_term($category_slug, 'product_cat', $cart_item['product_id'])) {
            $product_price = $cart_item['data']->get_price();
            $quantity = $cart_item['quantity'];
            $extra_charge += ($product_price * $percentage / 100) * $quantity;
        }
    }

    if ($extra_charge > 0) {
        WC()->cart->add_fee('Platform Fees (' . $percentage . '%)', $extra_charge, true, 'standard');
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
                                                <a href="<?php echo get_permalink($product->get_id()); ?>">
                                                    <?php echo $product->get_title(); ?>
                                                </a>
                                            </h3>
                                            <p>Turkey</p>
                                        </div>
                                        <div class="product-amout">
                                            <?php echo get_woocommerce_currency_symbol() . $product->get_price(); ?>
                                        </div>
                                    </div>
                                    <p>
                                        <?php echo mb_substr(wp_strip_all_tags($product->get_description()), 0, 80) . '...'; ?>
                                    </p>
                                    <div class="product-sku">
                                        <?php echo $product->get_sku(); ?>
                                    </div>
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
function display_custom_product_variations()
{
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


// Checkout 24-03-25
add_action('woocommerce_before_checkout_form', function () {
    ?>
    <!-- <div class="delever_to_couple_wrap">
        <input type="checkbox" id="deliver_to_couple" name="deliver_to_couple" checked>
        <label for="deliver_to_couple">Ship To Couple</label>
    </div> -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
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


function hide_lifafa_class_for_other_users()
{
    if (is_user_logged_in() && isset($_GET['original_user_id'])) {
        $current_user = get_current_user_id();
        $original_user_id = intval($_GET['original_user_id']);

        if ($current_user !== $original_user_id) {
            echo '<style>.lifafa_count_sec { display: none !important; }</style>';
        }
    }
}
add_action('wp_head', 'hide_lifafa_class_for_other_users');


function display_amazon_product($data, $original_user_id, $user_id = null)
{
    if (!$user_id)
        $user_id = get_current_user_id();

    global $wpdb;
    // var_dump($data);
    // $original_user_id = intval($_GET['original_user_id']);
    $table_name = $wpdb->prefix . 'purchased_submissions';
    $product_id = $data['product_id'];
    $price = $data['display_price'] ?? get_field('price', $product_id);
    $link = $data['link'] ?? get_field('product_link', $product_id);

    // Check if purchased
    $is_purchased = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d LIMIT 1",
        $user_id,
        $product_id
    ));
    $current_user = wp_get_current_user();

    // Thumbnail
    $thumbnail_url = get_the_post_thumbnail_url($product_id, 'full');
    if (!$thumbnail_url) {
        $thumbnail_url = get_template_directory_uri() . '/assets/images/default_gift.png';
    }

    // Format price
    if ($price) {
        $price = trim($price);
        $price = str_replace(',', '', $price);
        $price = rtrim($price, '.');
        $price = preg_replace('/[^\d.]/', '', $price);
        $price = (float) $price;
        $formatted_price = number_format($price, 0, '.', ',');
    } else {
        $formatted_price = '0';
    }
    ?>

    <div class="box presonalised-item <?php echo $is_purchased ? 'disabled' : ''; ?>"
        data-price="<?php echo esc_html($price); ?>">

        <div class="product-img <?php echo $original_user_id . 'img-' . esc_attr($product_id); ?>">
            <a href="<?php echo esc_url(get_permalink($product_id)); ?>" target="_blank">
                <img decoding="async" src="<?php echo esc_url($thumbnail_url); ?>"
                    alt="<?php echo esc_attr(get_the_title($product_id)); ?>">
            </a>

            <?php if ($is_purchased): ?>
                <div class="product-status-wrapper">
                    <span class="product-status">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                        PURCHASED
                    </span>
                </div>
            <?php endif; ?>

            <?php if (is_user_logged_in() && !$is_purchased && !$original_user_id): ?>
                <button class="custom_button remove_amazon_product" data-product_id="<?php echo esc_attr($product_id); ?>">
                    <i aria-hidden="true" class="fas fa-trash"></i>
                </button>
            <?php endif; ?>
        </div>

        <div class="product-info new-product-title-cls-adding">
            <div class="wishlist-amazon-product-cls">
                <div class="unique-product-cls-adding">
                    <div class="product-name new-product-name-all-cls-adding">
                        <h3 class="product-title">
                            <a href="<?php echo esc_url(get_permalink($product_id)); ?>" target="_blank">
                                <?php echo esc_html(get_the_title($product_id)); ?>
                            </a>
                        </h3>
                        <p>
                            <span class="woocommerce-Price-amount amount">
                                <bdi>
                                    <span class="woocommerce-Price-currencySymbol">₹</span>
                                    <?php echo esc_html($formatted_price); ?>
                                </bdi>
                            </span>
                        </p>
                    </div>

                    <div class="product-detail new-product-detail-cls all-type-of-cls-adding">
                        <div class="sold-via-cls">
                            <p class="product-detail-name new-fulfill-amazon-cls">Sold via Amazon</p>
                        </div>
                        <div class="may-via-cls">
                            <p class="product-detail-name new-fulfill-amazon-cls">We may earn a small commission</p>
                        </div>
                        <p class="product-quantity"></p>
                    </div>
                </div>

                <div class="product-action-buttons-flex">
                    <?php if (isset($original_user_id) && !empty($original_user_id) && $current_user->ID != $original_user_id): ?>
                        <a class="custom_button buy-amazon <?php echo $is_purchased ? 'disabled' : ''; ?>"
                            data-amazon-url="<?php echo esc_url($link); ?>">
                            Buy ON Amazon
                        </a>

                        <a href="javascript:void(0);" class="mark-purchased-btn"
                            data-product-id="<?php echo esc_attr($product_id); ?>">
                            <button class="custom_button mark-us-purchased-cls">Mark as purchased</button>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

