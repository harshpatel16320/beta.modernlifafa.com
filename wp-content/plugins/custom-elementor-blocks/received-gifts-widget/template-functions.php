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
        wp_send_json_error(array('message' => 'User must log in to add product to their personalized page.'));
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
    $variation_id = null;
    if ($product && $product->is_type('variable')) {
        $variation_id = get_matching_variation($product_id, $saved_attributes);
    }
    error_log("Matching Variation ID: " . ($variation_id ? $variation_id : "None"));
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    error_log("Quantity Requested: $qty");
    if ($click_action == 'add') {
        $product_exists = false;
        foreach ($personalized_products as &$item) {
            if ($item['product_id'] == $product_id && $item['variation_id'] == $variation_id) {
                $item['quantity'] += $qty;
                $product_qty[$product_id . '_' . $variation_id] = $item['quantity'];
                $product_exists = true;
                error_log("Updated existing product in personalized list. New quantity: " . $item['quantity']);
                break;
            }
        }
        if (!$product_exists) {
            $personalized_products[] = [
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $qty,
                'attributes' => $saved_attributes
            ];
            $product_qty[$product_id . '_' . $variation_id] = $qty;
            error_log("Added new product variation to personalized list.");
        }
        update_user_meta($user_id, '_personalized_products', $personalized_products);
        update_user_meta($user_id, '_product_quantity', $product_qty);
        error_log("Updated user meta for personalized products and quantities.");
        ob_clean();
        wp_send_json_success(array('message' => 'Product added to your wishlist.'));
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
        update_user_meta($user_id, '_personalized_products', array_values($personalized_products)); // Reindex array
        update_user_meta($user_id, '_product_quantity', $product_qty);
        error_log("Updated user meta after removal.");
        wp_send_json_success(array('message' => 'Product variation removed from your personalized page.'));
    }
    exit;
}
add_action('wp_ajax_add_to_personalized', 'add_to_personalized');
add_action('wp_ajax_nopriv_add_to_personalized', 'add_to_personalized');

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
    if (isset($_POST['account_holder_name'])) {
        update_user_meta($user_id, 'account_holder_name', sanitize_text_field($_POST['account_holder_name']));
    }
    if (isset($_POST['account_number'])) {
        update_user_meta($user_id, 'account_number', sanitize_text_field($_POST['account_number']));
    }
    if (isset($_POST['our_story'])) {
        update_user_meta($user_id, 'our_story', sanitize_text_field($_POST['our_story']));
    }
    if (isset($_POST['bank_name'])) {
        update_user_meta($user_id, 'bank_name', sanitize_text_field($_POST['bank_name']));
    }
    if (isset($_POST['bank_ifsc'])) {
        update_user_meta($user_id, 'bank_ifsc', sanitize_text_field($_POST['bank_ifsc']));
    }
    if (isset($_POST['bank_city'])) {
        update_user_meta($user_id, 'bank_city', sanitize_text_field($_POST['bank_city']));
    }
    if (isset($_POST['our_story'])) {
        update_user_meta($customer_id, 'our_story', sanitize_text_field($_POST['our_story']));
    }
    // if (isset($_POST['user_banner'])) {
    //     update_user_meta($user_id, 'selected_banner', sanitize_text_field($_POST['user_banner']));
    // }
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
