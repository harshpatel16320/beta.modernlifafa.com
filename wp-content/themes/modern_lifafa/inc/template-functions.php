<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package modern_lifafa
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function modern_lifafa_body_classes($classes)
{
    // Adds a class of hfeed to non-singular pages.
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Adds a class of no-sidebar when there is no sidebar present.
    if (!is_active_sidebar('sidebar-1')) {
        $classes[] = 'no-sidebar';
    }

    return $classes;
}
add_filter('body_class', 'modern_lifafa_body_classes');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function modern_lifafa_pingback_header()
{
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
    }
}
add_action('wp_head', 'modern_lifafa_pingback_header');


// function add_to_personalized() {
//     $product_id = intval($_POST['product_id']);
//     $click_action = $_POST['click_action'];
//     $user_id = get_current_user_id();
//     if ($user_id && $product_id) {
//         error_log("Processing request for User ID: $user_id, Product ID: $product_id");
//         $personalized_products = get_user_meta($user_id, '_personalized_products', true);
//         $product_qty = get_user_meta($user_id, '_product_quantity', true);
//         if (!is_array($personalized_products)) {
//             $personalized_products = array();
//             error_log("No previous personalized products found. Initializing empty array.");
//         }
//         error_log('hello called'.print_r($personalized_products));
//         if (!is_array($product_qty)) {
//             $product_qty = array();
//             error_log("No previous product quantity data found. Initializing empty array.");
//         }
//         $saved_attributes = get_user_meta($user_id, '_product_attributes_' . $product_id, true);
// 		$product = wc_get_product($product_id);
// 		if ($product->is_type('variable')) {
// 			$variation_id = get_matching_variation($product_id, $saved_attributes);
// 		}
//         error_log("Matching Variation ID: " . ($variation_id ? $variation_id->get_id() : "None"));
//         $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
//         error_log("Quantity Requested: $qty");
//         if ($click_action == 'add') {
//             $product_exists = false;
//             foreach ($personalized_products as $item) {
//                 error_log(print_r($item));
//                 if ($item['product_id'] == $product_id && $item['variation_id'] == $variation_id) {
//                     $item['quantity'] += $qty;
//                     $product_qty[$product_id . '_' . $variation_id] = $item['quantity'];
//                     $product_exists = true;
//                     error_log("Updated existing product in personalized list. New quantity: " . $item['quantity']);
//                     break;
//                 }
//             }
//             if (!$product_exists) {
//                 $personalized_products[] = array(
//                     'product_id'   => $product_id,
//                     'variation_id' => $variation_id,
//                     'quantity'     => $qty,
//                     'attributes'   => $saved_attributes
//                 );
//                 $product_qty[$product_id . '_' . $variation_id] = $qty;
//                 error_log("Added new product variation to personalized list.");
//             }
//             update_user_meta($user_id, '_personalized_products', $personalized_products);
//             update_user_meta($user_id, '_product_quantity', $product_qty);
//             error_log("Updated user meta for personalized products and quantities.");
//             wp_send_json_success(array('message' => 'Product added or updated in your personalized page.'));
//         } elseif ($click_action == 'remove') {
//             $removed = false;
//             foreach ($personalized_products as $key => $item) {
//                 if ($item['product_id'] == $product_id && $item['variation_id'] == $variation_id) {
//                     unset($personalized_products[$key]);
//                     unset($product_qty[$product_id . '_' . $variation_id]);
//                     $removed = true;
//                     error_log("Removed product variation from personalized list.");
//                     break;
//                 }
//             }
//             if (!$removed) {
//                 error_log("Product variation not found for removal.");
//             }
//             update_user_meta($user_id, '_personalized_products', array_values($personalized_products)); // Reindex array
//             update_user_meta($user_id, '_product_quantity', $product_qty);
//             error_log("Updated user meta after removal.");
//             wp_send_json_success(array('message' => 'Product variation removed from your personalized page.'));
//         }
//     } else {
//         error_log("Invalid request: Missing User ID or Product ID.");
//     } 
//     wp_send_json_error(array('message' => 'Users must log in to add products to their personalized page.'));
//     exit;
// }
function add_to_personalized()
{
    if (!isset($_POST['product_id'], $_POST['click_action'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $click_action = sanitize_text_field($_POST['click_action']);
    $user_id = get_current_user_id();

    if (!$user_id || !$product_id) {
        error_log("Invalid request: Missing User ID or Product ID.");
        wp_send_json_error(array('message' => 'Please log in or create an account to start building your wishlist!'));
        exit;
    }

    error_log("Processing request for User ID: $user_id, Product ID: $product_id");

    $personalized_products = get_user_meta($user_id, '_personalized_products', true);
    $product_qty = get_user_meta($user_id, '_product_quantity', true);

    if (!is_array($personalized_products))
        $personalized_products = [];
    if (!is_array($product_qty))
        $product_qty = [];

    $saved_attributes = get_user_meta($user_id, '_product_attributes_' . $product_id, true);
    $product = wc_get_product($product_id);
    $variation_id = intval($_POST['variation_id']);

    // if ($product && $product->is_type('variable')) {
    //     $variation_id = get_matching_variation($product_id, $saved_attributes);
    // }

    if ($product && $product->is_type('variable')) {
        // $variation_id = get_matching_variation($product_id, $saved_attributes);
        if (empty($saved_attributes) || !is_array($saved_attributes)) {
            wp_send_json_error(array('message' => 'Please select a product variation before adding to wishlist.'));
            exit;
        }

        // if (!$variation_id) {
        //     ob_clean();
        //     wp_send_json_error(array('message' => 'No matching variation found. Please select valid product options.'));
        //     exit;
        // }
    }

    error_log("Matching Variation ID: " . ($variation_id ? $variation_id : "None"));

    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    error_log("Quantity Requested: $qty");

    if ($click_action == 'add') {
        $matched = false;

        foreach ($personalized_products as &$item) {
            if (
                $item['product_id'] == $product_id &&
                $item['variation_id'] == $variation_id &&
                $item['attributes'] == $saved_attributes
            ) {
                $item['quantity'] = $qty;
                $product_qty[$product_id . '_' . $variation_id] = $qty;
                $matched = true;
                error_log("Updated quantity for existing product variation.");
                break;
            }
        }

        if (!$matched) {
            $personalized_products[] = [
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $qty,
                'attributes' => $saved_attributes
            ];
            $product_qty[$product_id . '_' . $variation_id] = $qty;
            // error_log("Added new variation for product.");
        }

        update_user_meta($user_id, '_personalized_products', $personalized_products);
        update_user_meta($user_id, '_product_quantity', $product_qty);
        error_log("Updated user meta for personalized products and quantities.");
        ob_clean();
        // wp_send_json_success(array('message' => 'Product added to your wishlist.'));

    } elseif ($click_action == 'remove') {
        $removed = false;
        foreach ($personalized_products as $key => $item) {
            if ($item['product_id'] == $product_id && $item['variation_id'] == $variation_id) {
                unset($personalized_products[$key]);
                unset($product_qty[$product_id . '_' . $variation_id]);
                $removed = true;
                error_log("Removed product variation from personalized list.");
                break;
            }
        }
        if (!$removed) {
            error_log("Product variation not found for removal.");
        }

        update_user_meta($user_id, '_personalized_products', array_values($personalized_products));
        update_user_meta($user_id, '_product_quantity', $product_qty);
        error_log("Updated user meta after removal.");
        wp_send_json_success(array('message' => 'Product variation removed from your personalized page.'));
    }
    exit;
}
add_action('wp_ajax_add_to_personalized', 'add_to_personalized');
add_action('wp_ajax_nopriv_add_to_personalized', 'add_to_personalized');


// Remove from personalised
function remove_from_personalized()
{
    if (!isset($_POST['product_id1'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
        exit;
    }

    $product_id = intval($_POST['product_id1']);
    $user_id = get_current_user_id();

    if (!$user_id || !$product_id) {
        wp_send_json_error(array('message' => 'User must log in to remove products.'));
        exit;
    }

    $personalized_products = get_user_meta($user_id, '_personalized_products', true);
    if (!is_array($personalized_products)) {
        wp_send_json_error(array('message' => 'No personalized products found.'));
        exit;
    }

    foreach ($personalized_products as $key => $item) {
        if ($item['product_id'] == $product_id) {
            unset($personalized_products[$key]);
            delete_user_meta($user_id, '_custom_price_' . $product_id);
            break;
        }
    }

    update_user_meta($user_id, '_personalized_products', array_values($personalized_products));

    wp_send_json_success(array('message' => 'Product removed successfully.'));
    exit;
}

add_action('wp_ajax_remove_from_personalized', 'remove_from_personalized');
add_action('wp_ajax_nopriv_remove_from_personalized', 'remove_from_personalized');

function add_to_amazon_personalized()
{
    error_log('AJAX triggered for user ' . get_current_user_id() . ' product ' . $_POST['product_id']);

    if (!isset($_POST['product_id'], $_POST['click_action'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $click_action = sanitize_text_field($_POST['click_action']);
    $user_id = get_current_user_id();
    $amazon_url = isset($_POST['amazon_url']) ? trim(wp_unslash($_POST['amazon_url'])) : '';
    // ensure $amazon_url is sanitized (but keep full url for LIKE queries)
    $amazon_url = esc_url_raw($amazon_url);

    error_log("Added product to wishlist for user $user_id, product $product_id");

    if (!$user_id || !$product_id) {
        error_log("Invalid request: Missing User ID or Product ID.");
        wp_send_json_error(['message' => 'Please log in or create an account to start building your wishlist!', 'status' => 'not_logged_in']);
        exit;
    }

    error_log("Processing request for User ID: $user_id, Product ID: $product_id");

    $personalized_products = get_user_meta($user_id, '_personalized_amazon_products', true);
    $product_qty = get_user_meta($user_id, '_product_quantity', true);

    if (!is_array($personalized_products))
        $personalized_products = [];
    if (!is_array($product_qty))
        $product_qty = [];

    $saved_attributes = get_user_meta($user_id, '_product_attributes_' . $product_id, true);
    $product = wc_get_product($product_id);
    // $variation_id = intval($_POST['variation_id']);

    // if ($product && $product->is_type('variable')) {
    //     $variation_id = get_matching_variation($product_id, $saved_attributes);
    // }

    // if ($product && $product->is_type('variable')) {
    //     // $variation_id = get_matching_variation($product_id, $saved_attributes);
    //     if (empty($saved_attributes) || !is_array($saved_attributes)) {
    //         wp_send_json_error(array('message' => 'Please select a product variation before adding to wishlist.'));
    //         exit;
    //     }

    //     // if (!$variation_id) {
    //     //     ob_clean();
    //     //     wp_send_json_error(array('message' => 'No matching variation found. Please select valid product options.'));
    //     //     exit;
    //     // }
    // }

    // error_log("Matching Variation ID: " . ($variation_id ? $variation_id : "None"));

    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    error_log("Quantity Requested: $qty");

    if ($click_action === 'add') {
        //  1. Check if product already exists
        $already_exists = false;
        foreach ($personalized_products as $item) {
            if (isset($item['product_id']) && $item['product_id'] == $product_id) {
                $already_exists = true;
                error_log("Already exists 1? " . ($already_exists ? 'YES' : 'NO'));

                break;
            }
        }

        //  2. If not found yet, check via WordPress meta_query (no raw SQL)
        if (!$already_exists && !empty($amazon_url)) {
            $existing_posts = get_posts([
                'post_type' => 'amazon_product',    // adjust if your post type differs
                'post_status' => 'any',
                'author' => intval($user_id),    // restrict to current user
                'meta_query' => [
                    [
                        'key' => 'product_link',
                        'value' => $amazon_url,
                        'compare' => 'LIKE',           // use LIKE if tracking tags may differ; use '=' if exact match guaranteed
                    ],
                ],
                'fields' => 'ids',
                'numberposts' => 1,
            ]);

            if (!empty($existing_posts)) {
                // found a post by this user with same product_link => treat as duplicate
                $already_exists = true;
                error_log("Already exists 2? " . ($already_exists ? 'YES' : 'NO'));

            }
        }


        if ($already_exists) {
            wp_send_json_error(['message' => 'This product is already in your wishlist.', 'status' => 'already_exists']);
            exit; // ✅ stop further execution
        } else {
            // Add new product
            $personalized_products[] = [
                'product_id' => $product_id,
                'time' => current_time('Y-m-d H:i:s'),
            ];
            error_log('---- New AJAX call started at ' . microtime(true) . ' ----');

            update_user_meta($user_id, '_personalized_amazon_products', $personalized_products);

            wp_send_json_success(['message' => 'Product added to your wishlist.']);
            exit;
        }
    } elseif ($click_action == 'remove') {
        $removed = false;
        foreach ($personalized_products as $key => $item) {
            if ($item['product_id'] == $product_id && $item['variation_id'] == $variation_id) {
                unset($personalized_products[$key]);
                unset($product_qty[$product_id . '_' . $variation_id]);
                $removed = true;
                error_log("Removed product variation from personalized list.");
                break;
            }
        }
        if (!$removed) {
            error_log("Product variation not found for removal.");
        }

        update_user_meta($user_id, '_personalized_products', array_values($personalized_products));
        update_user_meta($user_id, '_product_quantity', $product_qty);
        error_log("Updated user meta after removal.");
        wp_send_json_success(array('message' => 'Product variation removed from your personalized page.'));
    }
    exit;
}
add_action('wp_ajax_add_to_amazon_personalized', 'add_to_amazon_personalized');
add_action('wp_ajax_nopriv_add_to_amazon_personalized', 'add_to_amazon_personalized');

// Remove amazon product from personalised
function remove_from_amazon_personalized()
{
    if (!isset($_POST['product_id1'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $product_id = intval($_POST['product_id1']);
    $user_id = get_current_user_id();
    // echo $user_id;

    if (!$user_id || !$product_id) {
        wp_send_json_error(['message' => 'User must be logged in and product must be valid.']);
    }

    // Get current wishlist
    $personalized_products = get_user_meta($user_id, '_personalized_amazon_products', true);

    if (!is_array($personalized_products) || empty($personalized_products)) {
        wp_send_json_error(['message' => 'Your wishlist is empty.']);
    }

    $removed = false;
    foreach ($personalized_products as $key => $item) {
        if (isset($item['product_id']) && intval($item['product_id']) === $product_id) {
            unset($personalized_products[$key]);
            $removed = true;
            break;
        }
    }

    if (!$removed) {
        $args = [
            'post_type' => 'amazon_product',
            'author' => $user_id,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
        ];

        $user_posts = get_posts($args);

        // var_dump($user_posts);

        if (!empty($user_posts) && in_array($product_id, $user_posts)) {
            wp_delete_post($product_id, true); // permanently delete post
            $removed = true;
        }
    }


    if ($removed) {
        update_user_meta($user_id, '_personalized_amazon_products', array_values($personalized_products));
        wp_send_json_success(['message' => 'Product removed from your wishlist.']);
    } else {
        wp_send_json_error(['message' => 'Product not found in your wishlist.']);
    }
}

add_action('wp_ajax_remove_from_amazon_personalized', 'remove_from_amazon_personalized');
add_action('wp_ajax_nopriv_remove_from_amazon_personalized', 'remove_from_amazon_personalized');



// Allow .ico files
function allow_custom_upload_mimes($mimes)
{
    $mimes['ico'] = 'image/x-icon';
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_custom_upload_mimes');

add_action('woocommerce_save_account_details', 'save_custom_account_fields');
function save_custom_account_fields($user_id)
{
    if (isset($_POST['account_first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['account_first_name']));
    }
    if (isset($_POST['account_last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['account_last_name']));
    }
    if (isset($_POST['account_phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['account_phone']));
    }
    if (isset($_FILES['profile_picture'])) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $file = $_FILES['profile_picture'];
        $upload_overrides = array(
            'test_form' => false
        );
        $upload = wp_handle_upload($file, $upload_overrides);
        if (!isset($upload['error']) && isset($upload['url'])) {
            update_user_meta($user_id, 'profile_picture', $upload['url']);
            echo "Profile picture uploaded successfully!";
        } else {
            echo "There was an error uploading the file.";
        }
    }
    if (isset($_POST['account_partner_fname'])) {
        update_user_meta($user_id, 'partner_fname', sanitize_text_field($_POST['account_partner_fname']));
    }
    if (isset($_POST['account_partner_lname'])) {
        update_user_meta($user_id, 'partner_lname', sanitize_text_field($_POST['account_partner_lname']));
    }
    if (isset($_POST['account_secondary_email'])) {
        update_user_meta($user_id, 'secondary_email', sanitize_email($_POST['account_secondary_email']));
    }
    if (isset($_POST['account_wedding_venue'])) {
        update_user_meta($user_id, 'wedding_venue', sanitize_text_field($_POST['account_wedding_venue']));
    }
    if (isset($_POST['account_wedding_date'])) {
        update_user_meta($user_id, 'wedding_date', sanitize_text_field($_POST['account_wedding_date']));
    }
    if (isset($_POST['shipping_street'])) {
        update_user_meta($user_id, 'shipping_street', sanitize_text_field($_POST['shipping_street']));
    }
    if (isset($_POST['shipping_city'])) {
        update_user_meta($user_id, 'shipping_city', sanitize_text_field($_POST['shipping_city']));
    }
    if (isset($_POST['shipping_state'])) {
        update_user_meta($user_id, 'shipping_state', sanitize_text_field($_POST['shipping_state']));
    }
    if (isset($_POST['shipping_zipcode'])) {
        update_user_meta($user_id, 'shipping_zipcode', sanitize_text_field($_POST['shipping_zipcode']));
    }
    if (isset($_POST['shipping_country'])) {
        update_user_meta($user_id, 'shipping_country', sanitize_text_field($_POST['shipping_country']));
    }
    // if (isset($_POST['account_holder_name'])) {
    //     update_user_meta($user_id, 'account_holder_name', sanitize_text_field($_POST['account_holder_name']));
    // }
    // if (isset($_POST['account_number'])) {
    //     update_user_meta($user_id, 'account_number', sanitize_text_field($_POST['account_number']));
    // }
    // if (isset($_POST['our_story'])) {
    //     update_user_meta($user_id, 'our_story', sanitize_text_field($_POST['our_story']));
    // }
    // if (isset($_POST['bank_name'])) {
    //     update_user_meta($user_id, 'bank_name', sanitize_text_field($_POST['bank_name']));
    // }
    // if (isset($_POST['bank_ifsc'])) {
    //     update_user_meta($user_id, 'bank_ifsc', sanitize_text_field($_POST['bank_ifsc']));
    // }
    // if (isset($_POST['bank_city'])) {
    //     update_user_meta($user_id, 'bank_city', sanitize_text_field($_POST['bank_city']));
    // }
    if (isset($_POST['our_story'])) {
        update_user_meta($customer_id, 'our_story', sanitize_text_field($_POST['our_story']));
    }
    if (isset($_POST['user_banner'])) {
        $url = get_field('default_banner', 'options');
        update_user_meta($user_id, 'selected_banner', $url);
    }
}

// AJAX Handler to Save Selected Banner
function save_user_banner()
{
    check_ajax_referer('save_user_banner_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
    }

    $user_id = get_current_user_id();
    $banner_url = isset($_POST['banner_url']) ? esc_url_raw($_POST['banner_url']) : '';

    if ($banner_url) {
        update_user_meta($user_id, 'selected_banner', $banner_url);
        wp_send_json_success(['message' => 'Banner updated successfully']);
    } else {
        wp_send_json_error(['message' => 'Invalid banner URL']);
    }
}
add_action('wp_ajax_save_user_banner', 'save_user_banner');

// For  price filter in product is more than 20000

// add_action('woocommerce_product_query', 'custom_price_filter_query');
// function custom_price_filter_query($q)
// {
//     if (isset($_GET['price'])) {
//         $price_range = sanitize_text_field($_GET['price']);

//         if ($price_range === '20000-plus') {
//             $meta_query = $q->get('meta_query');
//             $meta_query[] = array(
//                 'key' => '_price',
//                 'value' => 20000,
//                 'compare' => '>=',
//                 'type' => 'NUMERIC'
//             );
//             $q->set('meta_query', $meta_query);
//         } elseif (strpos($price_range, '-') !== false) {
//             list($min_price, $max_price) = explode('-', $price_range);
//             $meta_query = $q->get('meta_query');
//             $meta_query[] = array(
//                 'key' => '_price',
//                 'value' => array((float) $min_price, (float) $max_price),
//                 'compare' => 'BETWEEN',
//                 'type' => 'NUMERIC'
//             );
//             $q->set('meta_query', $meta_query);
//         }
//     }
// }

add_filter('dokan_get_seller_order_status', 'show_processing_orders_in_vendor_dashboard');

function show_processing_orders_in_vendor_dashboard($statuses)
{
    // Add 'processing' to allowed order statuses
    if (!in_array('processing', $statuses)) {
        $statuses[] = 'processing';
    }
    return $statuses;
}

add_filter('dokan_get_seller_orders_args', function ($args) {
    $args['post_status'] = ['wc-processing', 'wc-completed', 'wc-pending', 'wc-on-hold', 'wc-failed'];
    return $args;
});



// Cron 
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_12_hours'])) {
        $schedules['every_12_hours'] = [
            'interval' => 12 * 60 * 60, // 12 hours in seconds
            'display' => __('Every 12 Hours')
        ];
    }
    return $schedules;
});

// 2️⃣ Schedule cron job (runs immediately once, then every 12 hours)
add_action('init', function () {
    if (!wp_next_scheduled('update_amazon_products_cron')) {
        // Schedule first run after 12 hours
        wp_schedule_event(time(), 'every_12_hours', 'update_amazon_products_cron');
        // Run immediately once now
        // update_amazon_products_from_amazon();
    }
});

// 3️⃣ Attach the cron action
add_action('update_amazon_products_cron', 'update_amazon_products_from_amazon');

// function update_amazon_products_from_amazon()
// {
//     $args = [
//         'post_type' => 'amazon_product',
//         'post_status' => 'publish',
//         'posts_per_page' => -1,
//         'fields' => 'ids',
//     ];
//     $posts = get_posts($args);

//     if (empty($posts))
//         return;

//     foreach ($posts as $post_id) {
//         $url = get_field('product_link', $post_id);
//         if (empty($url))
//             continue;

//         $new_data = fetch_amazon_product_data($url);
//         if (!$new_data || isset($new_data['error']))
//             continue;

//         $updated = false;

//         // --- Title ---
//         $old_title = get_the_title($post_id);
//         if ($old_title !== $new_data['title']) {
//             wp_update_post(['ID' => $post_id, 'post_title' => $new_data['title']]);
//             $updated = true;
//         }

//         // --- Description ---
//         $old_desc = get_post_field('post_content', $post_id);
//         if ($old_desc !== $new_data['description']) {
//             wp_update_post(['ID' => $post_id, 'post_content' => $new_data['description']]);
//             $updated = true;
//         }

//         // --- Price ---
//         $old_price = get_field('price', $post_id);
//         if ($old_price !== $new_data['price']) {
//             update_field('price', $new_data['price'], $post_id);
//             $updated = true;
//         }

//         // --- Image ---
//         $thumb_id = get_post_thumbnail_id($post_id);
//         $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
//         // if ($thumb_url !== $new_data['image'] && !empty($new_data['image'])) {
//         //     require_once ABSPATH . 'wp-admin/includes/image.php';
//         //     require_once ABSPATH . 'wp-admin/includes/file.php';
//         //     require_once ABSPATH . 'wp-admin/includes/media.php';
//         //     $attach_id = media_sideload_image($new_data['image'], $post_id, $new_data['title'], 'id');
//         //     if (!is_wp_error($attach_id)) {
//         //         set_post_thumbnail($post_id, $attach_id);
//         //         $updated = true;
//         //     }
//         // }

//         if ($updated) {
//             update_post_meta($post_id, '_last_amazon_update', current_time('mysql'));
//             error_log(" Amazon Product ID #$post_id updated at " . current_time('mysql'));
//         }
//     }
// }


function update_amazon_products_from_amazon()
{
    $batch_size = 10; // process 10 products per batch
    $paged = 1;

    do {
        $args = [
            'post_type'      => 'amazon_product',
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'paged'          => $paged,
            'fields'         => 'ids',
        ];

        $posts = get_posts($args);

        if (empty($posts)) {
            break;
        }

        foreach ($posts as $post_id) {

            // ✅ Skip recently updated (last 12 hours)
            $last_update = get_post_meta($post_id, '_last_amazon_update', true);
            if ($last_update && strtotime($last_update) > strtotime('-12 hours')) {
                continue;
            }

            $url = get_field('product_link', $post_id);
            if (empty($url)) {
                continue;
            }

            $new_data = fetch_amazon_product_data($url);

            if (!$new_data || isset($new_data['error'])) {
                error_log("❌ Error fetching product ID {$post_id}");
                continue;
            }

            $updated = false;

            // --- Title ---
            $old_title = get_the_title($post_id);
            if (!empty($new_data['title']) && $old_title !== $new_data['title']) {
                wp_update_post([
                    'ID'         => $post_id,
                    'post_title' => $new_data['title']
                ]);
                $updated = true;
            }

            // --- Description ---
            $old_desc = get_post_field('post_content', $post_id);
            if (!empty($new_data['description']) && $old_desc !== $new_data['description']) {
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $new_data['description']
                ]);
                $updated = true;
            }

            // --- Price ---
            $old_price = get_field('price', $post_id);
            if (!empty($new_data['price']) && $old_price != $new_data['price']) {
                update_field('price', $new_data['price'], $post_id);
                $updated = true;
            }
            // --- Image (optional, keep disabled if heavy) ---
            /*
            $thumb_id = get_post_thumbnail_id($post_id);
            $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';

            if (!empty($new_data['image']) && $thumb_url !== $new_data['image']) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';

                $attach_id = media_sideload_image($new_data['image'], $post_id, $new_data['title'], 'id');

                if (!is_wp_error($attach_id)) {
                    set_post_thumbnail($post_id, $attach_id);
                    $updated = true;
                }
            }
            */
            if ($updated) {
                update_post_meta($post_id, '_last_amazon_update', current_time('mysql'));
                error_log("✅ Product #{$post_id} updated at " . current_time('mysql'));
            }
            sleep(1);
        }
        $paged++;
    } while (!empty($posts));
    error_log('✅ Amazon cron completed');
}


$event = wp_get_scheduled_event('update_amazon_products_cron');
if ($event) {
    error_log('Next run at: ' . date('Y-m-d H:i:s', $event->timestamp));
} else {
    error_log(' Event not scheduled');
}
// ---- 3 Scraper function ----
function fetch_amazon_product_data($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
        CURLOPT_HTTPHEADER => [
            "Accept-Language: en-US,en;q=0.9",
            "Accept-Encoding: identity"
        ]
    ]);
    $html = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if (!$html)
        return ['error' => $curl_error];

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);

    // --- Title ---
    $titleNode = $xpath->query('//span[@id="productTitle"]')->item(0);
    if (!$titleNode)
        return ['error' => 'No title found'];
    $title = trim($titleNode->textContent);

    // --- Price (with multiple fallbacks) ---
    $priceNode = $xpath->query('
        //span[@id="priceblock_ourprice"] |
        //span[@id="priceblock_dealprice"] |
        //span[@id="corePriceDisplay_desktop_feature_div"]//span[contains(@class,"a-offscreen")] |
        //span[contains(@class,"a-price-whole")] |
        //span[contains(@class,"a-price")]/span[contains(@class,"a-offscreen")]
    ')->item(0);

    $price = '';
    if ($priceNode) {
        $raw = trim($priceNode->textContent);
        // Remove currency symbols and commas
        $raw = preg_replace('/[^\d.,]/', '', $raw);
        // Handle cases like "1,299.00"
        $raw = str_replace(',', '', $raw);
        $price = (string) intval(floor(floatval($raw)));
    }

    // --- Description ---
    $descNode = $xpath->query('//div[@id="productDescription"]')->item(0);
    if (!$descNode)
        $descNode = $xpath->query('//div[@id="feature-bullets"]')->item(0);
    $description = $descNode ? $doc->saveHTML($descNode) : '';

    // --- Image ---
    $imageNode = $xpath->query('//img[@id="landingImage"]')->item(0);
    $image = $imageNode ? $imageNode->getAttribute('src') : '';
    if (!$image && preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $m)) {
        $image = $m[1];
    }

    return [
        'title' => $title,
        'price' => $price,
        'description' => $description,
        'image' => $image,
    ];
}
