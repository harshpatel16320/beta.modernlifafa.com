            <?php

            use AmpProject\Validator\Spec\Tag\Em;
            use AmpProject\Validator\Spec\Tag\Var_;

            if (!defined('ABSPATH')) {
                exit;
            }
            class Wishlist_Widget extends \Elementor\Widget_Base
            {
                public function get_name()
                {
                    return 'wishlist_widget';
                }
                public function get_title()
                {
                    return __('Wishlist Widget', 'custom-widgets');
                }
                public function get_icon()
                {
                    return 'eicon-heart';
                }
                public function get_categories()
                {
                    return ['general'];
                }
                /*
                * ----------------------------------
                * 
                *           * Existing Wishlist Render Function
                * 
                *           * ----------------------------------
                * 
                *           */
                public function get_scroll_html()
                {
                    ob_start();
                    $this->render();
                    return ob_get_clean();
                }
                protected function render()
                {
                    $user_id = isset($_GET['original_user_id']) ? $_GET['original_user_id'] : get_current_user_id();
                    $user = get_userdata($user_id);
                    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
                    $wedding_date = get_user_meta($user_id, 'wedding_date', true);
                    $personalized_products = get_user_meta($user_id, '_personalized_products', true) ?? [];
                    $has_personalized_products = !empty($personalized_products);
                    $custom_price = get_user_meta($user_id, '_custom_price_', true) ?? [];
                    // var_dump($personalized_products);
                    $product_qty = get_user_meta($user_id, '_product_quantity', true) ?? '';
                    $received_item = [];
                    $total_amount = 0;
                    // Fetch Orders Where User Has Received Gifts
                    $orders = wc_get_orders([
                        'limit' => -1,
                        'status' => ['completed', 'processing', 'confirm'],
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
                    //   var_dump($received_item_attributes);
            ?>
            <div class="wishlist-product-section">
                <div class="container">
                    <div class="wishlist-head">
                        <div class="add-gift-wrapper">
                            <div class="title-with-img">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/registry-picks.png"
                                        alt="title img">
                                </picture>
                                <h2>Registry Picks</h2>
                            </div>
                        </div>
                        <div class="sort-img-wrapper">
                            <?php if (!isset($_GET['original_user_id']) && is_user_logged_in()) { ?>
                            <a href="javascript:void(0)" class="box add-more add-product-registry"
                                data-bs-toggle="modal" data-bs-target="#scrap-product-modal">
                                <span>Add Gifts</span>
                                <i aria-hidden="true" class="fas fa-add"></i>
                            </a>
                            <?php } ?>
                            <!-- Category Dropdown -->
                            <div class="filter">
                                <?php
                                        $purchased = false;
                                        $original_user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : 0;
                                        ?>
                                <?php if (is_user_logged_in() && !$purchased && (!$original_user_id)): ?>
                                <!-- <input type="button" class="box add-more add_amazon_product" value="Add Amazon Product URL"> -->
                                <?php endif; ?>
                                <!-- Start category dropdown -->
                                <select id="category-filter" class="custome-selectbox all_categories">
                                    <option value="" selected>All Categories</option>
                                    <?php
                                            $wc_categories = get_terms([
                                                'taxonomy' => 'product_cat',
                                                'hide_empty' => false,
                                                'exclude' => [get_term_by('slug', 'uncategorized', 'product_cat')->term_id],
                                            ]);
                                            $amazon_categories = get_terms([
                                                'taxonomy' => 'amazon_category',
                                                'hide_empty' => false,
                                                'exclude' => [get_term_by('slug', 'uncategorized', 'amazon_category')->term_id],
                                            ]);
                                            $all_categories = [];
                                            if (!is_wp_error($wc_categories)) {
                                                foreach ($wc_categories as $cat) {
                                                    $all_categories[$cat->name] = [
                                                        'id' => $cat->term_id,
                                                        'taxonomy' => 'product_cat',
                                                        'name' => $cat->name,
                                                        'slug' => $cat->slug,
                                                    ];
                                                }
                                            }
                                            if (!is_wp_error($amazon_categories)) {
                                                foreach ($amazon_categories as $cat) {
                                                    if (!isset($all_categories[$cat->name])) {
                                                        $all_categories[$cat->name] = [
                                                            'id' => $cat->term_id,
                                                            'taxonomy' => 'amazon_category',
                                                            'name' => $cat->name,
                                                            'slug' => $cat->slug,
                                                        ];
                                                    }
                                                }
                                            }
                                            foreach ($all_categories as $category) {
                                                $value = esc_attr($category['slug']);
                                                echo '<option value="' . esc_attr($value) . '">' . esc_html($category['name']) . '</option>';
                                            }
                                            ?>
                                </select>
                                <!-- end category dropdown -->
                                <select id="sort-options" class="custome-selectbox">
                                    <option value="" disabled selected>Sort by</option>
                                    <option value="price-asc">Price: Low to High</option>
                                    <option value="price-desc">Price: High to Low</option>
                                </select>
                                <input type="hidden" value="<?php echo esc_attr($user_id); ?>" class="user_id">
                                <input type="hidden" value="<?php echo implode(',', $received_item); ?>"
                                    class="received_gift">
                                <input type="button" class="custom_button secondary_button clear_filter" value="Clear">
                            </div>
                        </div>
                    </div>
                    <!-- Product listing in wishlist -->
                    <!-- This product is user NOT created -->
                    <div class="product-boxes" id="wishlist-products">
                        <?php
                                ?>
                        <?php
                                // Infinite scrolling: number of cards rendered per request.
                                $wishlist_scroll_page = max(1, absint($_GET['wishlist_scroll_page'] ?? 1));
                                $wishlist_scroll_size = 12;
                                $wishlist_scroll_start = ($wishlist_scroll_page - 1) * $wishlist_scroll_size;
                                $wishlist_scroll_index = 0;
                                //  Start Woocommerce Product 
                                $original_user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : 0;
                                if ($personalized_products) {
                                    // echo "<pre>";
                                    // var_dump($personalized_products);
                                    // echo "</pre>";
                                    foreach (array_reverse($personalized_products) as $product_data) {
                                        if (!is_array($product_data) || !isset($product_data['product_id']))
                                            continue;
                                        $variation_image_url = null;
                                        $product_id = $product_data['product_id'];

                                        // Fix: Ensure attributes is always an array.
                                        $attributes = $product_data['attributes'] ?? [];

                                        if (is_string($attributes)) {
                                            $decoded = json_decode($attributes, true);

                                            if (is_array($decoded)) {
                                                $attributes = $decoded;
                                            } else {
                                                $attributes = maybe_unserialize($attributes);
                                            }
                                        }

                                        if (!is_array($attributes)) {
                                            $attributes = [];
                                        }

                                        $unique_key = $product_id . '_' . md5(wp_json_encode($attributes));
                                        $product = wc_get_product($product_id);
                                        if (!$product || $product->get_status() !== 'publish')
                                            continue;
                                        // Keep the original product order and markup; only limit the visible page.
                                        if (
                                            $wishlist_scroll_index++ < $wishlist_scroll_start ||
                                            $wishlist_scroll_index > $wishlist_scroll_start + $wishlist_scroll_size
                                        ) {
                                            continue;
                                        }
                                        $product_qty_all = get_user_meta($user_id, '_product_quantity', true);
                                        $saved_attributes = $attributes;
                                        $variation_key = $product_id . '_' . md5(json_encode($saved_attributes));
                                        $qty = isset($product_qty_all[$variation_key]) ? $product_qty_all[$variation_key] : $product_data['quantity'];
                                        $term_names_array = wp_get_post_terms($product_id, 'brand', ['fields' => 'names']);
                                        $current_user_id = get_current_user_id();
                                        $original_user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : 0;
                                        $user_custom_price = get_user_meta($user_id, '_custom_price_' . $product_id, true);
                                        $display_price = '';
                                        $product_url = $product->get_permalink();
                                        if (isset($_GET['original_user_id'])) {
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
                                            $match = true;
                                            foreach ($available_variations as $variation) {
                                                $get_variation_id = '';
                                                foreach ($saved_attributes as $attribute_name => $attribute_value) {
                                                    if (!empty($variation['attributes'][$attribute_name]) && $variation['attributes'][$attribute_name] !== $attribute_value) {
                                                        $match = false;
                                                        break;
                                                    }
                                                }
                                                if ($match) {
                                                    $saved_variation_id = $product_data['variation_id'];
                                                    if ($saved_variation_id != '' && isset($saved_variation_id) && !empty($saved_variation_id)) {
                                                        $variation_product = wc_get_product($saved_variation_id);
                                                    } else {
                                                        $variation_product = wc_get_product($variation['variation_id']);
                                                    }
                                                    if ($variation_product) {
                                                        $variation_price = $variation_product->get_price();
                                                        $matched_variation_id = $variation['variation_id'];
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
                                        $default_image = get_stylesheet_directory_uri() . '/assets/images/default_gift.png';
                                        $image_url = $variation_image_url ?? ($thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : $default_image);
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
                            <div class="product-img 01">
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
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg"
                                            alt="check">
                                        PURCHASED
                                    </span>
                                </div>
                                <?php }
                                                    } else { ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg"
                                            alt="check">
                                        PURCHASED
                                    </span>
                                </div>
                                <?php }
                                                elseif ($is_out_of_stock): ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">OUT OF STOCK</span>
                                </div>
                                <?php endif; ?>
                                <?php
                                                $purchased = false;
                                                if (array_intersect($check_ids, $received_item) && !has_term('cash', 'product_cat', $product->get_id())) {
                                                    if ($product->is_type('variable')) {
                                                        foreach ($received_item_attributes as $item) {
                                                            if ($item['variation_id'] == $matched_variation_id) {
                                                                $purchased = true;
                                                                break;
                                                            }
                                                        }
                                                    } else {
                                                        $purchased = true;
                                                    }
                                                }
                                                ?>
                                <?php if (is_user_logged_in() && !$purchased && (!$original_user_id)): ?>
                                <button class="custom_button remove_product"
                                    data-product_id="<?php echo esc_attr($product_id); ?>">
                                    <i aria-hidden="true" class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="product-info new-product-info-cls-adding wishlist-new-widget-cls-adding-typed">
                                <div class="unique-product-cls-adding">
                                    <div class="product-name">
                                        <h3 class="product-title">
                                            <?php if (array_intersect($check_ids, $received_item)) {
                                                                $product_url = "javascript:void(0)";
                                                            } ?>
                                            <a
                                                href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                        </h3>
                                        <?php
                                                        if (has_term('cash', 'product_cat', $product->get_id())) {
                                                            echo '<h3 class="cash-product-tags">' . "Guests can gift any amount" . '</h3>';
                                                        }
                                                        ?>
                                        <?php
                                                        if (!has_term('cash', 'product_cat', $product->get_id())) {
                                                            echo '<p>' . wc_price($display_price) . '</p>';
                                                        }
                                                        ?>
                                        <input type="hidden" name="variation_id"
                                            value="<?php echo esc_attr($matched_variation_id); ?>" />
                                    </div>
                                    <div class="product-detail">
                                        <p class="product-detail-name">
                                            <?php echo isset($term_names_array[0]) ? esc_html($term_names_array[0]) : ''; ?>
                                        </p>
                                        <?php if ($qty == 0 || $qty == '' || empty($qty)) {
                                                            $qty = 1;
                                                        } ?>
                                        <?php // if ((is_user_logged_in() && !array_intersect($check_ids, $received_item) && !has_term('cash', 'product_cat', $product->get_id())) && !$is_out_of_stock ){
                                                        // if ((!has_term('cash', 'product_cat', $product->get_id())) && !$is_out_of_stock ){
                                                        // }else 
                                                        if ($product->is_type('variable')) {
                                                            $qty_class = 'updt_qty_' . $matched_variation_id;
                                                        } else {
                                                            $qty_class = 'updt_qty_' . $product->get_id();
                                                        }
                                                        if (!has_term('cash', 'product_cat', $product->get_id())) { ?>
                                        <p class="product-quantity">Qty: <span
                                                class="<?php echo $qty_class; ?>"><?php echo esc_html($qty); ?></span>
                                        </p>
                                        <?php } ?>
                                    </div>
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
                                    <!-- <label>Contribute what you wish </label> -->
                                    <div class="input-with-rupee">
                                        <span class="rupee-symbol">₹</span>
                                        <input type="number" class="custom_amount_<?php echo esc_attr($unique_key); ?>"
                                            min="1" value="" placeholder="Enter Gift amount"
                                            oninput="toggleRupeeSymbol(this)">
                                    </div>
                                </div>
                                <?php }
                                                if (isset($original_user_id) && !empty($original_user_id) && $current_user_id != $original_user_id) {
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
                                <input type="hidden" name="attributes"
                                    id="hidden_attributes_<?php echo esc_attr($unique_key); ?>"
                                    value="<?php echo esc_attr(json_encode($saved_attributes)); ?>" />
                                <input type="hidden" name="quantity"
                                    id="hidden_quantity_<?php echo esc_attr($unique_key); ?>"
                                    value="<?php echo esc_attr($qty); ?>" />
                                <div class="top-side-cls-adding">
                                    <button type="submit" name="add-to-cart"
                                        value="<?php echo esc_attr($product_id); ?>"
                                        class="custom_button <?php if ($is_cash_product)
                                                                                            echo "is_cash_product"; ?> add_to_cart_from_wishlist"
                                        data-key="<?php echo esc_attr($unique_key); ?>"
                                        data-gift-for="<?php echo $_GET['original_user_id']; ?>">
                                        Add to Cart
                                    </button>
                                </div>
                                <?php
                                                    endif;
                                                } ?>
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
                                    <div class="qty-wrap new-add-qty-cls-adding-here">
                                        <?php
                                                            $stock_quantity = $product->get_stock_quantity();
                                                            $max_qty = $product->managing_stock() && !$product->is_on_backorder() ? $stock_quantity : '';
                                                            ?>
                                        <button type="button" class="qty-minus">−</button>
                                        <input type="number"
                                            data-attributes="<?php echo esc_attr(json_encode($saved_attributes)); ?>"
                                            class="personalized-product-qty"
                                            data-product_id="<?php echo esc_attr($product_id); ?>"
                                            data-variation_id="<?php echo isset($matched_variation_id) ? esc_attr($matched_variation_id) : ''; ?>"
                                            value="<?php echo esc_attr($qty); ?>" min="1"
                                            <?php if ($max_qty !== '')
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
                                // End Woocommerce Product  
                                // Amazon product Created by Admin 
                                $merged_products = [];
                                /**
                                *                      * 
                                *                      * 1️⃣ Personalized products from user_meta
                                *                      * 
                                *                      */
                                // echo $user_id. '  - ';
                                $personalized_amazon_products = get_user_meta($user_id, '_personalized_amazon_products', true) ?? [];
                                $current_user_id1 = get_current_user();
                                // echo $current_user_id1;
                                if (!empty($personalized_amazon_products)) {
                                    foreach ($personalized_amazon_products as $personalized_amazon_product) {
                                        $pro_id = $personalized_amazon_product['product_id'];
                                        $time = $personalized_amazon_product['time'] ?? '';
                                        $post = get_post($pro_id);
                                        if ($post && $post->post_status === 'publish') {
                                            $merged_products[] = [
                                                'product_id' => $pro_id,
                                                'time' => $time,
                                                'source' => 'personalized', // helps you identify later
                                            ];
                                        }
                                    }
                                }
                                /**
                                *                      * 
                                *                      * 2️⃣ Amazon products created by current user
                                *                      * 
                                *                      */
                                $args = [
                                    'post_type' => 'amazon_product',
                                    'author' => $user_id,
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish',
                                ];
                                $query = new WP_Query($args);
                                if ($query->have_posts()) {
                                    while ($query->have_posts()) {
                                        $query->the_post();
                                        $product_id = get_the_ID();
                                        // you can store post date or use custom ACF time if available
                                        $merged_products[] = [
                                            'product_id' => $product_id,
                                            'time' => get_the_date('Y-m-d H:i:s', $product_id),
                                            'source' => 'user',
                                        ];
                                    }
                                }
                                wp_reset_postdata();
                                /**
                                *                      * 
                                *                      * 3️⃣ Sort merged array by time ASC
                                *                      * 
                                *                      */
                                usort($merged_products, function ($a, $b) {
                                    return strtotime($a['time']) <=> strtotime($b['time']);
                                });
                                /**
                                *                      * 
                                *                      * 4️⃣ Loop sorted products and display
                                *                      * 
                                *                      */
                                foreach ($merged_products as $product) {
                                    $pro_id = $product['product_id'];
                                    if ('product' !== get_post_type($pro_id)) {
                                        if (get_post_status($pro_id) !== 'publish') {
                                            continue;
                                        }
                                        // Continue the same pagination across WooCommerce and Amazon products.
                                        if (
                                            $wishlist_scroll_index++ < $wishlist_scroll_start ||
                                            $wishlist_scroll_index > $wishlist_scroll_start + $wishlist_scroll_size
                                        ) {
                                            continue;
                                        }
                                        $price = get_field('price', $pro_id);
                                        if ($price === '' || $price === null || $price === false) {
                                            $price = get_post_meta($pro_id, 'price', true);
                                        }
                                        if (is_string($price)) {
                                            $price = preg_replace('/[^0-9.]/', '', str_replace(',', '', $price));
                                        }
                                        $price = is_numeric($price) ? (float) $price : 0;
                                        $link  = get_field('product_link', $pro_id);
                                        /**
                                        *                              * 
                                        *                              * Detect store from its ORIGINAL URL, not from an
                                        *                              * 
                                        *                              * affiliate destination or legacy "Amazon" label.
                                        *                              * 
                                        *                              * No remote HTTP request is made here.
                                        *                              * 
                                        *                              */
                                        $platform_name = trim((string) get_post_meta($pro_id, 'external_platform', true));
                                        $source_url = trim((string) get_post_meta($pro_id, '_ml_source_url', true));
                                        if (!$source_url) {
                                            $source_url = trim((string) get_post_meta($pro_id, 'product_link', true));
                                        }
                                        if (!$source_url && is_string($link)) {
                                            $source_url = trim($link);
                                        }
                                        if (!$source_url) {
                                            $source_url = trim((string) get_post_meta($pro_id, '_ml_buy_url', true));
                                        }
                                        $host = strtolower((string) wp_parse_url($source_url, PHP_URL_HOST));
                                        $host = preg_replace('/^www\./i', '', $host);
                                        $store_domains = [
                                            'amazon.in' => 'Amazon',
                                            'amazon.com' => 'Amazon',
                                            'meesho.com' => 'Meesho',
                                            'decathlon.in' => 'Decathlon',
                                            'decathlon.com' => 'Decathlon',
                                            'tradeindia.com' => 'TradeIndia',
                                            'flipkart.com' => 'Flipkart',
                                            'tataneu.com' => 'Tata Neu',
                                            'snapdeal.com' => 'Snapdeal',
                                            'myntra.com' => 'Myntra',
                                            'ajio.com' => 'AJIO',
                                            'nykaa.com' => 'Nykaa',
                                            'nykaafashion.com' => 'Nykaa Fashion',
                                            'tatacliq.com' => 'Tata CLiQ',
                                            'thesouledstore.com' => 'The Souled Store',
                                            'jiomart.com' => 'JioMart',
                                            'bigbasket.com' => 'BigBasket',
                                            'blinkit.com' => 'Blinkit',
                                            'zepto.com' => 'Zepto',
                                            'zeptonow\.com' => 'Zepto',
                                            'swiggy.com' => 'Swiggy Instamart',
                                            'firstcry.com' => 'FirstCry',
                                            'lenskart.com' => 'Lenskart',
                                            'pepperfry.com' => 'Pepperfry',
                                            '1mg.com' => 'Tata 1mg',
                                            'pharmeasy.in' => 'PharmEasy',
                                            'tirabeauty.com' => 'TiraBeauty',
                                            'croma.com' => 'Croma',
                                        ];
                                        if ($host) {
                                            foreach ($store_domains as $domain => $name) {
                                                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                                                    $platform_name = $name;
                                                    break;
                                                }
                                            }
                                            // A recognized platform from the existing theme helper.
                                            if (!$platform_name || strtolower($platform_name) === 'amazon') {
                                                if (function_exists('mlf_external_platform_name')) {
                                                    $detected = trim((string) mlf_external_platform_name($source_url));
                                                    if ($detected && $detected !== 'External Store') {
                                                        $platform_name = $detected;
                                                    }
                                                }
                                            }
                                            if (!$platform_name || $platform_name === 'External Store') {
                                                $base = explode('.', $host)[0];
                                                $platform_name = ucwords(str_replace(['-', '_'], ' ', $base));
                                            }
                                        }
                                        if (!$platform_name) {
                                            $platform_name = 'External Store';
                                        }
                                        global $wpdb;
                                        $table_name = $wpdb->prefix . 'purchased_submissions';
                                        $results = $wpdb->get_results(
                                            $wpdb->prepare(
                                                "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d LIMIT 1",
                                                $user_id,
                                                $pro_id
                                            )
                                        );
                                        // your HTML output logic reused below
                                    ?>
                        <div class="box presonalised-item <?php echo $results ? 'disabled' : ''; ?>"
                            data-price="<?php echo esc_html($price); ?>">
                            <div class="product-img 000">
                                <?php
                                                // $thumbnail_url = get_the_post_thumbnail_url($pro_id, 'full') ?: get_template_directory_uri() . '/assets/images/default_gift.png';
                                                $thumbnail_url = mlf_get_registry_product_image(
                                                    $pro_id,
                                                    'full'
                                                );
                                                ?>
                                <a href="<?php echo esc_url(get_permalink($pro_id)); ?>" target="_blank">
                                    <img src="<?php echo esc_url($thumbnail_url); ?>"
                                        alt="<?php echo esc_attr(get_the_title($pro_id)); ?>">
                                </a>
                                <?php
                                                if ($results): ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg"
                                            alt="check">
                                        PURCHASED
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php // if (is_user_logged_in() && !$results && (!$original_user_id)): ?>
                                <!-- <button class="custom_button remove_amazon_product"
                                    data-product_id="<?php echo $pro_id; ?>">
                                    <i aria-hidden="true" class="fas fa-trash"></i>
                                </button> -->
                                <?php // endif; ?>
                                <?php
                                    $registry_products = get_user_meta(
                                        get_current_user_id(),
                                        '_personalized_amazon_products',
                                        true
                                    );

                                    $registry_products = is_array($registry_products)
                                        ? $registry_products
                                        : [];

                                    $is_in_registry = false;

                                    foreach ($registry_products as $registry_item) {
                                        if (
                                            is_array($registry_item) &&
                                            isset($registry_item['product_id']) &&
                                            (int) $registry_item['product_id'] === (int) $pro_id
                                        ) {
                                            $is_in_registry = true;
                                            break;
                                        }
                                    }

                                    $is_product_author = (
                                        (int) get_post_field('post_author', $pro_id)
                                        === get_current_user_id()
                                    );

                                    $can_edit_external_gift = (
                                        is_user_logged_in()
                                        && !$results
                                        && !$original_user_id
                                        && get_post_type($pro_id) === 'amazon_product'
                                        && ($is_in_registry || $is_product_author)
                                    );
                                    ?>

                                <?php if (is_user_logged_in() && !$results && !$original_user_id): ?>

                                <div class="mlf-external-gift-actions">

                                    <?php if ($can_edit_external_gift): ?>
                                    <button type="button" class="custom_button mlf-edit-external-gift"
                                        data-product-id="<?php echo esc_attr($pro_id); ?>"
                                        data-product-name="<?php echo esc_attr(get_the_title($pro_id)); ?>"
                                        data-product-price="<?php echo esc_attr($price); ?>" title="Edit Gift"
                                        aria-label="Edit Gift">

                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </button>
                                    <?php endif; ?>

                                    <button type="button" class="custom_button remove_amazon_product"
                                        data-product_id="<?php echo esc_attr($pro_id); ?>" title="Remove Gift"
                                        aria-label="Remove Gift">

                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>

                                </div>

                                <?php endif; ?>
                            </div>
                            <div class="product-info new-product-title-cls-adding">
                                <div class="wishlist-amazon-product-cls">
                                    <div class="unique-product-cls-adding">
                                        <div class="product-name new-product-name-all-cls-adding">
                                            <h3 class="product-title">
                                                <a href="<?php echo esc_url(get_permalink($pro_id)); ?>"
                                                    target="_blank">
                                                    <?php echo get_the_title($pro_id); ?>
                                                </a>
                                            </h3>
                                            <?php if ($price && $price > 0): ?>
                                            <p><span
                                                    class="woocommerce-Price-amount222 amount <?php echo $results ? 'disabled' : ''; ?>">
                                                    <bdi><span class="woocommerce-Price-currencySymbol">₹</span>
                                                        <?php echo esc_html(number_format((float) preg_replace('/[^\d.]/', '', $price), 0, '.', ',')); ?>
                                                    </bdi>
                                                </span></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-detail new-product-detail-cls all-type-of-cls-adding">
                                            <div class="sold-via-cls">
                                                <p class="product-detail-name new-fulfill-amazon-cls">
                                                    Sold via <?php echo esc_html($platform_name); ?>
                                                </p>
                                            </div>
                                            <div class="may-via-cls">
                                                <?php
                                                                $is_amazon_platform = in_array(
                                                                    strtolower(trim($platform_name)),
                                                                    [
                                                                        'amazon',
                                                                        'amazon india',
                                                                        'amazon.in'
                                                                    ],
                                                                    true
                                                                );
                                                                ?>
                                                <?php if ($is_amazon_platform): ?>
                                                <div class="may-via-cls">
                                                    <p class="product-detail-name new-fulfill-amazon-cls">
                                                        We may earn a small commission on qualifying purchases.
                                                    </p>
                                                </div>
                                                <?php endif; ?>
                                                </p>
                                            </div>
                                            <p class="product-quantity"></p>
                                        </div>
                                    </div>
                                    <div class="product-action-buttons-flex">
                                        <?php if (isset($original_user_id) && !empty($original_user_id) && $current_user_id1 != $original_user_id) { ?>
                                        <!-- <a class="custom_button buy-amazon 
                                                            <?php if ($results):
                                                                    echo "disabled";
                                                                endif; ?>" data-amazon-url=" <?php echo esc_url($link); ?>">Buy
                                                                ON Amazon</a> -->
                                        <a class="custom_button buy-amazon 
                                                            <?php if ($results):
                                                                echo "disabled";
                                                            endif; ?>" data-amazon-url="<?php echo esc_url($link); ?>">
                                            Buy on <?php echo esc_html($platform_name); ?>
                                        </a>
                                        <a href="javascript:void(0);" class="mark-purchased-btn"
                                            data-product-id="<?php echo esc_attr($pro_id); ?>">
                                            <button class="custom_button mark-us-purchased-cls">Mark as
                                                purchased</button>
                                        </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                                    }
                                }
                                // $personalized_products - For woocommerce products array
                                //  $merged_products = get_user_meta($user_id, '_personalized_amazon_products', true) ?? [];
                                $amazon_products_only = [];
                                if (!empty($merged_products)) {
                                    foreach ($merged_products as $item) {
                                        $pro_id = $item['product_id'];
                                        $post_type = get_post_type($pro_id);
                                        if ($post_type === 'amazon_product') {
                                            $amazon_products_only[] = $item;
                                        }
                                    }
                                }
                                // print_r($amazon_products_only);
                                // echo '<pre>';
                                // echo "Current user ID: $user_id";
                                // print_r($personalized_amazon_products);
                                // echo '</pre>';
                                // if (empty($personalized_products)) {
                                //     echo '<p class="No-pro">No product found personalize</p>';
                                // }
                                if (empty($personalized_products) && empty($amazon_products_only)) {
                                    echo '<p class="no-pro-text">Your registry is ready to be curated. Explore our thoughtfully selected gifts and begin adding the pieces you love.</p>';
                                }
                                ?>
                    </div>
                    <?php if ($wishlist_scroll_page === 1): ?>
                    <div id="wishlist-scroll-trigger" aria-hidden="true"></div>
                    <?php endif; ?>
                    <script>
                    (function() {
                        function initWishlistInfiniteScroll() {
                            const root = document.getElementById('wishlist-products');
                            const trigger = document.getElementById('wishlist-scroll-trigger');
                            if (!root || !trigger || trigger.dataset.initialized) return;
                            trigger.dataset.initialized = '1';
                            let page = 1;
                            let busy = false;
                            let finished = false;
                            const observer = new IntersectionObserver(async (entries) => {
                                if (!entries[0].isIntersecting || busy || finished) return;
                                busy = true;
                                const nextPage = page + 1;
                                trigger.textContent = 'Loading more products...';
                                try {
                                    const url = new URL(window.location.href);
                                    url.searchParams.set('wishlist_scroll_page', String(nextPage));
                                    const response = await fetch(url.toString(), {
                                        method: 'GET',
                                        credentials: 'same-origin',
                                        cache: 'no-store',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });
                                    if (!response.ok) throw new Error('Unable to load wishlist: HTTP ' +
                                        response
                                        .status);
                                    const html = await response.text();
                                    const doc = new DOMParser().parseFromString(html, 'text/html');
                                    const newRoot = doc.querySelector('#wishlist-products');
                                    if (!newRoot) throw new Error(
                                        'Wishlist products container not found');
                                    const items = newRoot.querySelectorAll(':scope > .box');
                                    if (!items.length) {
                                        finished = true;
                                        observer.disconnect();
                                        trigger.remove();
                                        return;
                                    }
                                    const fragment = document.createDocumentFragment();
                                    items.forEach((item) => fragment.appendChild(item));
                                    root.appendChild(fragment);
                                    page = nextPage;
                                    root.dispatchEvent(new CustomEvent('wishlist:products-loaded', {
                                        bubbles: true,
                                        detail: {
                                            page: page,
                                            count: items.length
                                        }
                                    }));
                                    if (items.length < 12) {
                                        finished = true;
                                        observer.disconnect();
                                        trigger.remove();
                                    }
                                } catch (error) {
                                    console.error('Wishlist infinite scroll:', error);
                                    // Retry only after the user scrolls back to the trigger.
                                    observer.unobserve(trigger);
                                    const retry = () => {
                                        window.removeEventListener('scroll', retry);
                                        if (!finished && trigger.isConnected) observer.observe(
                                            trigger);
                                    };
                                    window.addEventListener('scroll', retry, {
                                        once: true,
                                        passive: true
                                    });
                                } finally {
                                    busy = false;
                                    if (trigger.isConnected) trigger.textContent = '';
                                }
                            }, {
                                rootMargin: '500px 0px',
                                threshold: 0
                            });
                            observer.observe(trigger);
                        }
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initWishlistInfiniteScroll);
                        } else {
                            initWishlistInfiniteScroll();
                        }
                    })();
                    </script>
                    <!-- End Product listing in wishlist -->
                </div>
            </div>
            <?php
                }
            }
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Wishlist_Widget());
            // Register Widget
            // function register_wishlist_widget($widgets_manager) {
            //     require_once(__DIR__ . '/wishlist-widget.php');
            //     $widgets_manager->register(new \Wishlist_Widget());
            // }
            // add_action('elementor/widgets/register', 'register_wishlist_widget');
            ?>
