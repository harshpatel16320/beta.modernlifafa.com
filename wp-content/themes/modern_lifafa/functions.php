<?php

/**
 * modern_lifafa functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package modern_lifafa
 */
if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.0');
}
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function modern_lifafa_setup()
{
    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on modern_lifafa, use a find and replace
     * to change 'modern_lifafa' to the name of your theme in all the template files.
     */
    load_theme_textdomain('modern_lifafa', get_template_directory() . '/languages');
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');
    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support('title-tag');
    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');
    // This theme uses wp_nav_menu() in one location.
    register_nav_menus(
        array(
            'menu-1' => esc_html__('Primary', 'modern_lifafa'),
        )
    );
    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );
    // Set up the WordPress core custom background feature.
    add_theme_support(
        'custom-background',
        apply_filters(
            'modern_lifafa_custom_background_args',
            array(
                'default-color' => 'ffffff',
                'default-image' => '',
            )
        )
    );
    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');
    /**
     * Add support for core custom logo.
     *
     * @link https://codex.wordpress.org/Theme_Logo
     */
    add_theme_support(
        'custom-logo',
        array(
            'height' => 250,
            'width' => 250,
            'flex-width' => true,
            'flex-height' => true,
        )
    );
}
add_action('after_setup_theme', 'modern_lifafa_setup');
/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function modern_lifafa_content_width()
{
    $GLOBALS['content_width'] = apply_filters('modern_lifafa_content_width', 640);
}
add_action('after_setup_theme', 'modern_lifafa_content_width', 0);
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function modern_lifafa_widgets_init()
{
    register_sidebar(
        array(
            'name' => esc_html__('Sidebar', 'modern_lifafa'),
            'id' => 'sidebar-1',
            'description' => esc_html__('Add widgets here.', 'modern_lifafa'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        )
    );
}
add_action('widgets_init', 'modern_lifafa_widgets_init');
/**
 * Enqueue scripts and styles.
 */
function modern_lifafa_scripts()
{
    wp_enqueue_style('modern_lifafa-style', get_stylesheet_uri(), array(), _S_VERSION);
    wp_style_add_data('modern_lifafa-style', 'rtl', 'replace');
    wp_enqueue_style('morden-lifafa-toaster', get_template_directory_uri() . '/assets/css/toastr.min.css', array(), _S_VERSION);
    wp_enqueue_style('new-style', get_template_directory_uri() . '/assets/css/new-style.css', array(), filemtime(get_template_directory() . '/assets/css/new-style.css'));
    wp_enqueue_style('morden-lifafa-cart', get_template_directory_uri() . '/assets/css/cart.css', array(), _S_VERSION);
    wp_enqueue_style('morden-lifafa-swiper', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', array(), _S_VERSION);
    wp_enqueue_script('morden-lifafa-swiper-bundle', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', array(), _S_VERSION, true);
    wp_enqueue_script('morden-lifafa-form-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js');
    wp_enqueue_script('modern_lifafa-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
    wp_enqueue_script('morden-lifafa-toaster', get_template_directory_uri() . '/assets/js/toastr.min.js', array(), _S_VERSION, true);
    wp_enqueue_script('modern_lifafa-custom-js', get_template_directory_uri() . '/js/custom.js', array(), _S_VERSION, true);
    wp_localize_script('modern_lifafa-custom-js', 'myData', array(
        'logoutUrl' => wp_logout_url(home_url()),
        'login_url' => wc_get_page_permalink('myaccount'),
        'homeUrlJs' => trailingslashit(home_url()) //  Ensures trailing slash
    ));
    wp_enqueue_script('modern_lifafa-development-js', get_template_directory_uri() . '/js/development.js', array(), _S_VERSION, true);
    wp_enqueue_script('modern_lifafa-custom-validation-js', get_template_directory_uri() . '/js/custom_validation.js', array(), _S_VERSION, true);
    // wp_localize_script('modern_lifafa-custom-js', 'ajaxurl', admin_url('admin-ajax.php'));
    wp_localize_script('modern_lifafa-custom-js', 'wishlist_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'wishlist_url' => home_url('/wishlist/'),
        'products_url' => home_url('/products/')
    ));
    // Enqueue Dokan shipment restriction script for vendors
    if (function_exists('dokan_is_vendor_dashboard') && dokan_is_vendor_dashboard()) {
        wp_enqueue_script('dokan-shipment-restrict', get_template_directory_uri() . '/js/dokan-shipment-restrict.js', array('jquery'), _S_VERSION, true);
        wp_localize_script('dokan-shipment-restrict', 'dokan_admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dokan_nonce')
        ));
    }
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'modern_lifafa_scripts');
// function modern_lifafa_enqueue_admin_scripts()
// {
//  wp_enqueue_script(
//      'modern_lifafa-admin-custom-js',
//      get_template_directory_uri() . '/js/admin_custom.js',
//      array(),
//      defined('_S_VERSION') ? _S_VERSION : false,
//      true
//  );
// }
// add_action('admin_enqueue_scripts', 'modern_lifafa_enqueue_admin_scripts');
function modern_lifafa_enqueue_admin_scripts($hook)
{
    // Only load on Amazon Product admin pages (optional filter)
    if (strpos($hook, 'amazon_product') === false) {
        return;
    }
    wp_enqueue_script(
        'modern_lifafa-admin-custom-js',
        get_template_directory_uri() . '/js/admin_custom.js',
        array('jquery'),
        defined('_S_VERSION') ? _S_VERSION : false,
        true
    );
    // Localize for nonce + ajaxurl
    wp_localize_script('modern_lifafa-admin-custom-js', 'amazonAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('add_amazon_product_action'),
    ));
}
add_action('admin_enqueue_scripts', 'modern_lifafa_enqueue_admin_scripts');
/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';
/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';
/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';
/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/development.php';
/**
 * Custom Payment.
 */
// require get_template_directory() . '/inc/custom-payment.php';
// require_once plugin_dir_path(__FILE__) . 'custom-elementor-blocks/wishlist-widget/wishlist-widget.php';
require get_template_directory() . '/inc/elementor_shortcodes.php';
/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
    require get_template_directory() . '/inc/jetpack.php';
}
function ajax_user_search()
{
    global $wpdb;
    $search_query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $search_terms = array_filter(explode(' ', $search_query));
    $user_ids = [];
    $meta_conditions = [];
    if (!empty($search_terms)) {
        // Searching in core WP user fields (username, email, display name)
        foreach ($search_terms as $term) {
            $user_query = new WP_User_Query([
                'search' => $term, // Exact word match
                'search_columns' => ['user_login', 'user_nicename', 'user_email', 'display_name'],
                'role__not_in' => ['Administrator'],
                'fields' => 'ID',
            ]);
            $user_ids = array_merge($user_ids, $user_query->get_results());
        }
        // Searching in user meta fields (first_name, last_name, partner_fname, partner_lname)
        foreach ($search_terms as $term) {
            // Ensuring exact word match (not partial)
            $meta_conditions[] = $wpdb->prepare(
                "(meta_key IN ('first_name', 'last_name', 'partner_fname', 'partner_lname') AND meta_value REGEXP %s)",
                '[[:<:]]' . $wpdb->esc_like($term) . '[[:>:]]'
            );
        }
        // Executing the meta query
        if (!empty($meta_conditions)) {
            $meta_query = "SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE " . implode(' OR ', $meta_conditions);
            $meta_results = $wpdb->get_col($meta_query);
        } else {
            $meta_results = [];
        }
        // Merge all user IDs (from WP_Query & direct DB search)
        $all_user_ids = array_unique(array_merge($user_ids, $meta_results));
        if (!empty($all_user_ids)) {
            $args = [
                'include' => $all_user_ids,
                'number' => 8, // Limit results
            ];
            $users = get_users($args);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $first_name = get_user_meta($user->ID, 'first_name', true);
                    $last_name = get_user_meta($user->ID, 'last_name', true);
                    $partner_fname = get_user_meta($user->ID, 'partner_fname', true);
                    $partner_lname = get_user_meta($user->ID, 'partner_lname', true);
                    $profile_picture = get_user_meta($user->ID, 'profile_picture', true);
                    echo '<li>';
                    echo '<a href="' . home_url() . '/wishlist/?couple_name=' . urlencode($first_name . '-and-' . $partner_fname) . '&original_user_id=' . $user->ID . '">';
                    if ($profile_picture) {
                        echo '<span><picture><img src="' . esc_url($profile_picture) . '" alt="couple image"></picture></span>';
                    }
                    echo '<strong>' . esc_html($first_name . ' ' . $last_name) . ' and ' . esc_html($partner_fname . ' ' . $partner_lname) . '</strong>';
                    echo '</a>';
                    echo '</li>';
                }
            } else {
                echo '<li>No users found.</li>';
            }
        } else {
            echo '<li>No users found.</li>';
        }
    } else {
        echo '<li>Please enter a search term.</li>';
    }
    wp_die();
}
add_action('wp_ajax_user_search', 'ajax_user_search');
add_action('wp_ajax_nopriv_user_search', 'ajax_user_search');
function custom_dynamic_bg()
{
    $user_id = isset($_GET['original_user_id']) ? $_GET['original_user_id'] : get_current_user_id();
    $backgroundImage = get_user_meta($user_id, 'selected_banner', true);
    if (empty($backgroundImage) || $backgroundImage == '') {
        $backgroundImage = get_field('default_banner', 'options');
    }
    $custom_css = "
        .dynamic-bg {
            background-image: url('$backgroundImage');
            background-repeat: no-repeat;
            background-size: cover;
        }
    ";
    wp_add_inline_style('modern_lifafa-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'custom_dynamic_bg');
function add_custom_user_role()
{
    $customer_role = get_role('customer');
    $customer_capabilities = $customer_role->capabilities;
    add_role('registered_customer', 'Registered Customer', $customer_capabilities);
}
add_action('init', 'add_custom_user_role');
add_action('woocommerce_registration_errors', 'check_email_before_registration', 10, 3);
function check_email_before_registration($errors, $username, $email)
{
    if (email_exists($email)) {
        $errors->add('email_exists', __('This email is already registered. Please use another email.', 'woocommerce'));
    }
    return $errors;
}
// add_action('user_register', 'save_custom_registration_fields');
add_action('woocommerce_created_customer', 'save_custom_registration_fields');
function save_custom_registration_fields($customer_id)
{
    if (isset($_POST['billing_first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
    }
    if (isset($_POST['billing_last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
    }
    if (isset($_POST['phone'])) {
        update_user_meta($customer_id, 'phone', sanitize_text_field($_POST['phone']));
    }
    if (isset($_POST['email'])) {
        update_user_meta($customer_id, 'email', sanitize_email($_POST['email']));
    }
    if (isset($_POST['password_1'])) {
        wp_set_password($_POST['password_1'], $customer_id);
    }
    if (isset($_POST['partner_fname'])) {
        update_user_meta($customer_id, 'partner_fname', sanitize_text_field($_POST['partner_fname']));
    }
    if (isset($_POST['partner_lname'])) {
        update_user_meta($customer_id, 'partner_lname', sanitize_text_field($_POST['partner_lname']));
    }
    if (isset($_POST['secondary_email'])) {
        update_user_meta($customer_id, 'secondary_email', sanitize_email($_POST['secondary_email']));
    }
    if (isset($_POST['wedding_venue'])) {
        update_user_meta($customer_id, 'wedding_venue', sanitize_text_field($_POST['wedding_venue']));
    }
    if (isset($_POST['wedding_date'])) {
        update_user_meta($customer_id, 'wedding_date', sanitize_text_field($_POST['wedding_date']));
    }
    if (isset($_POST['our_story'])) {
        update_user_meta($customer_id, 'our_story', sanitize_text_field($_POST['our_story']));
    }
    if (isset($_FILES['profile_image'])) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $file = $_FILES['profile_image'];
        $upload_overrides = array('test_form' => false);
        $upload = wp_handle_upload($file, $upload_overrides);
        if (!isset($upload['error']) && isset($upload['url'])) {
            update_user_meta($customer_id, 'profile_picture', $upload['url']);
        }
    }
    $user = new WP_User($customer_id);
    if ($user->has_cap('customer_capability') && in_array('customer', $user->roles)) {
        $user->set_role('registered_customer');
    }
}
function user_not_loggedin()
{
    if (!is_user_logged_in() && is_page('wishlist') && !isset($_GET['couple_name'])) {
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
    if (!is_user_logged_in() && is_page('password-change')) {
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
    if (!is_user_logged_in() && is_page('received-gifts')) {
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
    if (is_user_logged_in() && is_page('my-account')) {
        $user = wp_get_current_user();
        if (in_array('seller', (array) $user->roles)) {
            wp_safe_redirect(home_url('/dashboard'));
            exit();
        }
    }
    if (is_user_logged_in() && is_page('wishlist')) {
        $user = wp_get_current_user();
        if (in_array('seller', (array) $user->roles)) {
            wp_safe_redirect(home_url('/dashboard'));
            exit();
        }
        // exit;
    }
}
add_action('template_redirect', 'user_not_loggedin');
function redirect_after_account_update($user_id)
{
    if (!is_admin()) {
        wp_redirect(site_url('/wishlist/'));
        exit;
    }
}
add_action('woocommerce_save_account_details', 'redirect_after_account_update', 20, 1);
function redirect_to_wishlist_after_login($user_login, $user)
{
    if (!is_admin() && isset($user->roles)) {
        if (in_array('seller', (array) $user->roles)) {
            wp_safe_redirect(site_url('/dashboard/'));
        } else {
            wp_safe_redirect(site_url('/wishlist/'));
        }
        exit;
    }
}
add_action('wp_login', 'redirect_to_wishlist_after_login', 10, 2);
add_action(
    'wp_ajax_handle_gift_status',
    'handle_gift_status'
);
add_action(
    'wp_ajax_nopriv_handle_gift_status',
    'handle_gift_status'
);
function handle_gift_status()
{
    /*
     * -------------------------------------------------
     * 1. Validate request
     * -------------------------------------------------
     */
    $item_id = isset($_POST['item_id'])
        ? intval($_POST['item_id'])
        : 0;
    $order_id = isset($_POST['order_id'])
        ? intval($_POST['order_id'])
        : 0;
    $status = isset($_POST['status'])
        ? sanitize_text_field($_POST['status'])
        : '';
    if (!$item_id || !$order_id) {
        wp_send_json_error([
            'message' => 'Invalid request.'
        ]);
    }
    /*
     * -------------------------------------------------
     * 2. User must be logged in
     * -------------------------------------------------
     */
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'You must be logged in.'
        ], 401);
    }
    /*
     * -------------------------------------------------
     * 3. Get order
     * -------------------------------------------------
     */
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error([
            'message' => 'Order not found.'
        ]);
    }
    /*
     * -------------------------------------------------
     * 4. Get item
     * -------------------------------------------------
     */
    $item = $order->get_item($item_id);
    if (!$item) {
        wp_send_json_error([
            'message' => 'Item not found.'
        ]);
    }
    /*
     * -------------------------------------------------
     * 5. TRANSFER
     * -------------------------------------------------
     */
    if ($status === 'transfer') {
        $gift_status = $item->get_meta('_gift_status', true);
        if ($gift_status === 'confirm') {
            wp_send_json_error([
                'message' => 'This gift has already been transferred.'
            ]);
        }
        // Update gift item status
        $item->update_meta_data(
            '_gift_status',
            'confirm'
        );
        $item->save();
        // Update WooCommerce order status
        $order->set_status('confirm');
        $order->save();
        wp_send_json_success([
            'message' => 'Transferred'
        ]);
    }
    /*
     * -------------------------------------------------
     * Existing non-cash actions
     * -------------------------------------------------
     */
    if ($status === 'shipp' || $status === 'accept') {
        // Update gift item status
        $item->update_meta_data('_gift_status', 'confirm');
        $item->save();
        // Update WooCommerce admin order status
        $order->update_status(
            'confirm',
            'Gift marked as shipped from Received Gifts page.'
	);
        // Gift shipped successfully
        wp_send_json_success([
            'message' => 'SHIPPED',
            'order_status' => $order->get_status()
        ]);
    }
    /*
     * Unknown action.
     */
    wp_send_json_error([
        'message' => 'Invalid gift status.'
    ]);
}
function custom_remove_dokan_dashboard_nav($urls)
{
    unset($urls['withdraw']);
    unset($urls['settings']);
    unset($urls['visit-store']);
    return $urls;
}
add_filter('dokan_get_dashboard_nav', 'custom_remove_dokan_dashboard_nav');
/**
 * Restrict vendor shipment updates until order status is 'confirm'
 */
// function restrict_shipment_update_until_confirm($shipment_data, $shipment_id, $order_id)
// {
//  $order = wc_get_order($order_id);
//  if (!$order) {
//      return $shipment_data;
//  }
//  $order_status = $order->get_status();
//  // Allow shipment updates only if order status is 'confirm'
//  if ($order_status !== 'confirm') {
//      wp_send_json_error([
//          'message' => 'You can only add shipment details after order status is confirmed. Current status: ' . wc_get_order_status_name($order_status)
//      ]);
//      wp_die();
//  }
//  return $shipment_data;
// }
// add_filter('dokan_pro_shipping_status_update', 'restrict_shipment_update_until_confirm', 10, 3);
/**
 * AJAX handler to check if vendor can ship - called before shipment form submission
 */
// function check_if_can_ship_order()
// {
//  if (!isset($_POST['order_id']) || !isset($_POST['nonce'])) {
//      wp_send_json_error(['message' => 'Invalid request']);
//  }
//  $order_id = intval($_POST['order_id']);
//  $order = wc_get_order($order_id);
//  if (!$order) {
//      wp_send_json_error(['message' => 'Order not found']);
//  }
//  $order_status = $order->get_status();
//  if ($order_status === 'confirm') {
//      wp_send_json_success(['can_ship' => true]);
//  } else {
//      wp_send_json_error([
//          'can_ship' => false,
//          'message' => 'Order must be in "Confirm" status to add tracking. Current status: ' . wc_get_order_status_name($order_status)
//      ]);
//  }
// }
// add_action('wp_ajax_check_if_can_ship', 'check_if_can_ship_order');
// add_action('wp_ajax_nopriv_check_if_can_ship', 'check_if_can_ship_order');

// Add a custom field before the order notes in the checkout page
add_action('woocommerce_before_order_notes', 'add_custom_checkout_field');
function add_custom_checkout_field($checkout)
{
    echo '<div id="my_custom_checkout_field"><h2>' . __('Gift Message') . '</h2>';
    woocommerce_form_field('gift_message', array(
        'type' => 'textarea',
        'class' => array('my-field-class form-row-wide'),
        'label' => __('Enter your gift message'),
        'placeholder' => __('Enter your gift message here'),
    ), $checkout->get_value('gift_message'));
    echo '</div>';
}
add_action('woocommerce_checkout_update_order_meta', 'save_gift_message_checkout_field');
function save_gift_message_checkout_field($order_id)
{
    if (!empty($_POST['gift_message'])) {
        update_post_meta($order_id, 'gift_message', sanitize_textarea_field($_POST['gift_message']));
    }
}
// Auto suggest
function search_location()
{
    if (isset($_POST['search'])) {
        $search = urlencode($_POST['search']);
        $url = "https://nominatim.openstreetmap.org/search?q=$search&format=json&addressdetails=1&limit=10";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'modern-lifafa/1.0 (your-email@example.com)');
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $results = [];
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $result) {
                $address = $result['address'];
                $results[] = [
                    "display_name" => $result['display_name'] ?? '',
                    "street" => $address['road'] ?? '',
                    "city" => $address['city'] ?? ($address['town'] ?? ($address['village'] ?? '')),
                    "state" => $address['state'] ?? '',
                    "zipcode" => $address['postcode'] ?? '',
                    "country" => $address['country'] ?? ''
                ];
            }
        }
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($results);
        exit();
    }
}
add_action('wp_ajax_search_location', 'search_location');
add_action('wp_ajax_nopriv_search_location', 'search_location'); // Allow non-logged-in users
add_filter('woocommerce_cart_item_price', 'update_cart_item_price', 10, 3);
function update_cart_item_price($price, $cart_item, $cart_item_key)
{
    if (isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
        $variation = new WC_Product_Variation($cart_item['variation_id']);
        $price = $variation->get_price_html();
    }
    return $price;
}
function get_variation_price()
{
    $product_id = intval($_POST['product_id']);
    $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();
    if (!empty($attributes)) {
        $user_id = get_current_user_id();
        update_user_meta($user_id, '_product_attributes_' . $product_id, $attributes);
    }
    $product = wc_get_product($product_id);
    if ($product && $product->is_type('variable')) {
        $variation_id = $product->get_matching_variation($attributes);
        if ($variation_id) {
            $variation = new WC_Product_Variation($variation_id);
            $image_id = $variation->get_image_id();
            $image_url = wp_get_attachment_url($image_id);
            wp_send_json_success(array(
                'variation_id' => $variation_id,
                'price_html' => $variation->get_price_html(),
                'image_url' => $image_url,
            ));
        }
    }
    wp_send_json_error(array('message' => 'No matching variation found.'));
    wp_die();
}
add_action('wp_ajax_get_variation_price', 'get_variation_price');
add_action('wp_ajax_nopriv_get_variation_price', 'get_variation_price');
function custom_woocommerce_add_to_cart()
{
    error_log('custom_woocommerce_add_to_cart triggered.');
    if (!function_exists('WC') || !WC()->cart) {
        error_log('WooCommerce is not initialized or cart is not available.');
        wp_send_json_error(['message' => 'WooCommerce is not initialized or cart is not available.']);
        wp_die();
    }
    if (!isset($_POST['product_id'])) {
        error_log('Missing product ID.');
        wp_send_json_error(['message' => 'Missing product ID.']);
        wp_die();
    }
    $product_id = intval($_POST['product_id']);
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $quantity = max(1, isset($_POST['quantity']) ? intval($_POST['quantity']) : 1);
    $gift_for = isset($_POST['gift_for']) ? sanitize_text_field($_POST['gift_for']) : '';
    $custom_amount = isset($_POST['custom_amount']) ? sanitize_text_field($_POST['custom_amount']) : '';
    error_log("Product ID: $product_id, Variation ID: $variation_id, Quantity: $quantity, Gift For: $gift_for, Custom Amount: $custom_amount");
    $product = wc_get_product($product_id);
    if (!$product) {
        error_log('Invalid product.');
        wp_send_json_error(['message' => 'Invalid product.']);
        wp_die();
    }
    $cart_item_data = [];
    if (!empty($gift_for)) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            if (isset($cart_item['gift_for']) && $cart_item['gift_for'] != $gift_for) {
                error_log('Gift for mismatch in cart.');
                wp_send_json_error(['message' => 'You can gift to only one couple at a time.']);
                wp_die();
            }
        }
        $cart_item_data['gift_for'] = $gift_for;
        error_log('Gift for accepted: ' . $gift_for);
    }
    $variation_id = 0;
    if (!empty($custom_amount)) {
        if (!is_numeric($custom_amount) || $custom_amount <= 0) {
            wp_send_json_error(['message' => 'Invalid custom amount.']);
        }
        $cart_item_data['custom_price'] = floatval($custom_amount);
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    } else {
        foreach ($attributes as $key => $val) {
            $cart_item_data['variation'][$key] = sanitize_text_field($val);
        }
        $variation_id = intval($_POST['variation_id']) ?: 0;
    }
    error_log('Variation Attributes: ' . json_encode($variation_attributes));
    $found_in_cart = false;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (
            $cart_item['product_id'] == $product_id &&
            $cart_item['variation_id'] == $variation_id &&
            $cart_item['gift_for'] == $gift_for &&
            (isset($cart_item['variation']) ? $cart_item['variation'] : []) == $variation_attributes
        ) {
            WC()->cart->set_quantity($cart_item_key, $quantity);
            $found_in_cart = true;
            error_log('Product found in cart, updated quantity.');
            break;
        }
    }
    if (!$found_in_cart) {
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_attributes, $cart_item_data);
        error_log('Product added to cart.');
    }
    ob_start();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product = $cart_item['data'];
        $image = $_product->get_image_id() ? wp_get_attachment_url($_product->get_image_id()) : get_template_directory_uri() . '/assets/images/default_image.png';
        $title = $_product->get_title();
        $price = wc_price($_product->get_price());
        $total = wc_price($_product->get_price() * $cart_item['quantity']);
        $attributes_html = '';
        if (!empty($cart_item['variation'])) {
            $attributes_html .= '<ul class="wc-block-components-product-details">';
            foreach ($cart_item['variation'] as $name => $value) {
                $label = wc_attribute_label(str_replace('attribute_', '', $name));
                $attributes_html .= '<li><span class="wc-block-components-product-details__name">' . esc_html($label) . ':</span> <span class="wc-block-components-product-details__value">' . esc_html($value) . '</span></li>';
            }
            $attributes_html .= '</ul>';
        }
        echo '<tr class="wc-block-cart-items__row">
            <td class="wc-block-cart-item__image"><a href="' . get_permalink($cart_item['product_id']) . '"><img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '"></a></td>
            <td class="wc-block-cart-item__product">
                <a class="wc-block-components-product-name" href="' . get_permalink($cart_item['product_id']) . '">' . esc_html($title) . '</a>
                <span class="price">' . $price . '</span>
                ' . $attributes_html . '
            </td>
            <td class="wc-block-cart-item__total">' . $total . '</td>
        </tr>';
    }
    $cart_items_html = ob_get_clean();
    $fragments = [
        '.wc-block-cart-items' => $cart_items_html,
        '.wc-block-components-totals-item__value' => WC()->cart->get_cart_total(),
    ];
    error_log('Sending JSON response with cart fragments.');
    wp_send_json_success(['fragments' => $fragments]);
    wp_die();
}
add_action('wp_ajax_custom_woocommerce_add_to_cart', 'custom_woocommerce_add_to_cart');
add_action('wp_ajax_nopriv_custom_woocommerce_add_to_cart', 'custom_woocommerce_add_to_cart');
add_filter('woocommerce_add_cart_item_data', 'add_registered_user_id_to_cart', 10, 2);
function add_registered_user_id_to_cart($cart_item_data, $product_id)
{
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $personalized_products = get_user_meta($user_id, '_personalized_products', true);
        // Check if the product is in the user's wishlist
        if (!empty($personalized_products) && in_array($product_id, $personalized_products)) {
            $cart_item_data['registered_user_id'] = $user_id;
        }
    }
    return $cart_item_data;
}
add_action('woocommerce_after_order_notes', 'add_original_user_id_field');
function add_original_user_id_field($checkout)
{
    if (isset($_GET['original_user_id'])) {
        $original_user_id = sanitize_text_field($_GET['original_user_id']);
        // error_log('Original User ID from URL: ' . $original_user_id);  // Debugging: Log the ID
        echo '<input type="hidden" name="original_user_id" value="' . esc_attr($original_user_id) . '">';
    }
}
add_action('woocommerce_checkout_create_order', 'save_original_user_id_in_order_meta', 10, 2);
function save_original_user_id_in_order_meta($order, $data)
{
    if (isset($_POST['original_user_id'])) {
        $original_user_id = intval($_POST['original_user_id']);  // Extract the ID from the POST reques
        $order->update_meta_data('_wc_order_attribution_session_entry', $original_user_id);
    }
}
add_action('woocommerce_thankyou', 'send_email_on_order_complete', 10, 1);

add_action('woocommerce_checkout_before_customer_details', 'add_deliver_to_couple_checkbox_before_billing');

function add_deliver_to_couple_checkbox_before_billing() {
    $checked = WC()->checkout->get_value('deliver_to_couple');

    // Default checked on initial page load.
    if (!isset($_POST['deliver_to_couple']) && !wp_doing_ajax() && empty($_POST)) {
        $checked = 1;
    }

    echo '<div id="my_deliver_to_couple_wrapper">';

    woocommerce_form_field('deliver_to_couple', array(
        'type' => 'checkbox',
        'label' => __('Ship To Couple', 'woocommerce'),
        'label_class' => array('custom-checkbox'),
        'class' => array('form-row-wide'),
        'required' => false,
    ), $checked);

    echo '</div>';
}

function save_deliver_to_couple_order_meta($order, $data)
{
    $deliver_to_couple = isset($_POST['deliver_to_couple']) ? 'yes' : 'no';
    $order->update_meta_data('deliver_to_couple', $deliver_to_couple);
}
add_action('woocommerce_checkout_create_order', 'save_deliver_to_couple_order_meta', 10, 2);
/**
 * Identify couple from shipping address and save couple user ID
 */
add_action('woocommerce_checkout_create_order', 'ml_save_couple_user_id', 20, 2);
function ml_save_couple_user_id($order, $data)
{
    $deliver_to_couple = isset($_POST['deliver_to_couple'])
        ? sanitize_text_field(wp_unslash($_POST['deliver_to_couple']))
        : '';
    if ($deliver_to_couple !== '1' && $deliver_to_couple !== 'yes') {
        return;
    }
    $shipping_first_name = sanitize_text_field(
        wp_unslash($_POST['shipping_first_name'] ?? '')
    );
    $shipping_last_name = sanitize_text_field(
        wp_unslash($_POST['shipping_last_name'] ?? '')
    );
    $shipping_city = sanitize_text_field(
        wp_unslash($_POST['shipping_city'] ?? '')
    );
    $shipping_postcode = sanitize_text_field(
        wp_unslash($_POST['shipping_postcode'] ?? '')
    );
    // error_log('===== COUPLE IDENTIFICATION =====');
    // error_log('Shipping first name: ' . $shipping_first_name);
    // error_log('Shipping last name: ' . $shipping_last_name);
    // error_log('Shipping city: ' . $shipping_city);
    // error_log('Shipping postcode: ' . $shipping_postcode);
    if (!$shipping_first_name || !$shipping_last_name) {
        error_log('Couple identification failed: shipping name missing');
        return;
    }
    $users = get_users([
        'meta_query' => [
            'relation' => 'AND',
            [
                'key'     => 'first_name',
                'value'   => $shipping_first_name,
                'compare' => '=',
            ],
            [
                'key'     => 'last_name',
                'value'   => $shipping_last_name,
                'compare' => '=',
            ],
        ],
        'number' => 10,
        'fields' => 'ID',
    ]);
    // error_log('Matching couple users: ' . print_r($users, true));
    if (empty($users)) {
        error_log('Couple user NOT FOUND');
        return;
    }
    $couple_user_id = 0;
    foreach ($users as $user_id) {
        $user_city = get_user_meta(
            $user_id,
            'shipping_city',
            true
        );
        // error_log("Checking user {$user_id}, city: {$user_city}");
        if (
            $shipping_city &&
            $user_city &&
            strtolower(trim($shipping_city)) === strtolower(trim($user_city))
        ) {
            $couple_user_id = $user_id;
            break;
        }
    }
    if (!$couple_user_id && count($users) === 1) {
        $couple_user_id = $users[0];
    }
    if (!$couple_user_id) {
        error_log('Couple user could not be uniquely identified');
        return;
    }
    $order->update_meta_data('_couple_user_id', $couple_user_id);
    $order->update_meta_data('_wc_order_attribution_session_entry', $couple_user_id);
    /*
     * Get Couple Name.
     */
    $first_name = get_user_meta($couple_user_id, 'first_name', true);
    $last_name = get_user_meta($couple_user_id, 'last_name', true);
    $partner_fname = get_user_meta($couple_user_id, 'partner_fname', true);
    $partner_lname = get_user_meta($couple_user_id, 'partner_lname', true);
    $person_one = trim($first_name . ' ' . $last_name);
    $person_two = trim($partner_fname . ' ' . $partner_lname);
    $names = array_filter([$person_one, $person_two,]);
    $couple_full_name = implode(' & ', $names);
    /*
     * Save Couple Name.
     */
    $order->update_meta_data('_couple_full_name', $couple_full_name);
    /*
     * Save Registry ID.
     *
     * Currently using Couple WordPress User ID
     * as the Registry ID.
     */
    $order->update_meta_data('_couple_registry_id', $couple_user_id);
    // error_log(
    //     'COUPLE DETAILS SAVED: Order=' .
    //     $order->get_id() .
    //     ' CoupleUserID=' .
    //     $couple_user_id .
    //     ' CoupleName=' .
    //     $couple_full_name .
    //     ' RegistryID=' .
    //     $couple_user_id
    // );
    // error_log('COUPLE USER IDENTIFIED: ' . $couple_user_id);
}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_deliver_to_couple_admin_order_meta', 10, 1);
function display_deliver_to_couple_admin_order_meta($order)
{
    $deliver_to_couple = $order->get_meta('deliver_to_couple');
    $display_value = ($deliver_to_couple === 'yes') ? 'Yes' : 'No';
    echo '<p><strong>' . __('Ship To Couple') . ':</strong> ' . $display_value . '</p>';
    $gift_message = $order->get_meta('gift_message');
    if (!empty($gift_message)) {
        echo '<p><strong>' . __('Gift Message', 'woocommerce') . ':</strong><br>' . $gift_message . '</p>';
    }
}
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
add_filter('template_include', 'override_woocommerce_single_product_template', 100);
function override_woocommerce_single_product_template($template)
{
    if (is_singular('product')) {
        return get_stylesheet_directory() . '/woocommerce/single-product.php';
    }
    if (is_product_category()) {
        // wp_redirect(get_permalink(wc_get_page_id('shop')));
        return get_stylesheet_directory() . '/woocommerce/archive-product.php';
    }
    if (is_tax('brand')) {
        // Use the same template as product categories
        $template = locate_template('woocommerce/archive-product.php');
    }
    return $template;
}
// Banner image 
function my_banner_images_admin_scripts($hook)
{
    if ($hook !== 'toplevel_page_banner-images-settings') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('banner-images-script', get_template_directory_uri() . '/banner-images.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'my_banner_images_admin_scripts');
function my_banner_images_settings_page()
{
    add_menu_page(
        'Banner Images',
        'Banner Images',
        'manage_options',
        'banner-images-settings',
        'my_banner_images_settings_callback',
        'dashicons-format-image',
        20
    );
}
add_action('admin_menu', 'my_banner_images_settings_page');
function my_banner_images_settings_callback()
{
?>
<div class="wrap">
    <h1>Choose Banner Images</h1>
    <form method="post" action="options.php">
        <?php
            settings_fields('banner_images_group');
            do_settings_sections('banner-images-settings');
            submit_button();
            ?>
    </form>
</div>
<?php
}
function my_banner_images_settings_init()
{
    register_setting('banner_images_group', 'banner_images');
    add_settings_section('banner_images_section', 'Select Four Images', null, 'banner-images-settings');
    for ($i = 1; $i <= 4; $i++) {
        add_settings_field(
            "banner_image_$i",
            "Banner Image $i",
            function () use ($i) {
                $banner_images = get_option('banner_images', []);
                $image_id = isset($banner_images[$i]) ? intval($banner_images[$i]) : '';
                $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    ?>
<input type="hidden" id="banner_image_<?php echo $i; ?>" name="banner_images[<?php echo $i; ?>]"
    value="<?php echo $image_id; ?>">
<button type="button" class="upload-banner-image button" data-target="banner_image_<?php echo $i; ?>">Choose
    Image</button>
<button type="button" class="remove-banner-image button"
    style="display: <?php echo $image_id ? 'inline-block' : 'none'; ?>;">Remove</button>
<br><br>
<img id="preview_banner_image_<?php echo $i; ?>" src="<?php echo esc_url($image_url); ?>"
    style="max-width:150px; <?php echo $image_url ? '' : 'display:none;'; ?>">
<?php
            },
            'banner-images-settings',
            'banner_images_section'
        );
    }
}
add_action('admin_init', 'my_banner_images_settings_init');
function custom_empty_cart_message()
{
    if (wc_get_page_id('shop') > 0) {
        echo '<div class="custom-empty-cart">';
        echo '<h2>Your cart is empty!</h2>';
        echo '<p>Explore our products and add items to your cart.</p>';
        echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="button">Shop Now</a>';
        echo '</div>';
    }
}
add_action('woocommerce_cart_is_empty', 'custom_empty_cart_message');
add_filter('body_class', function ($classes) {
    if (WC()->cart->is_empty()) {
        $classes[] = 'cart-is-empty';
    } else {
        $classes[] = 'cart-has-items';
    }
    return $classes;
});
// After change password redirect to login page 
add_action('woocommerce_before_customer_login_form', function () {
    if (isset($_GET['password_change']) && $_GET['password_change'] == 'true') {
        wc_add_notice("Password changed successfully! Please log in with your new password.", "success");
    }
});
// // After registration redirect to login page with success message 
function redirect_after_registration($user_id)
{
    if (!is_admin()) {
        wp_safe_redirect(site_url('/wishlist/'));
    }
}
add_action('user_register', 'redirect_after_registration');
function display_registration_success_message()
{
    if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
        echo '<div class="woocommerce-message">Registration successful! Please log in.</div>';
    }
}
add_action('woocommerce_before_customer_login_form', 'display_registration_success_message');
add_filter('woocommerce_default_address_fields', function ($fields) {
    if (is_checkout()) {
        foreach ($fields as $key => $field) {
            if (strpos($key, 'shipping_') !== false) {
                $fields[$key]['required'] = false;
            }
        }
    }
    return $fields;
}, 20);
add_filter('woocommerce_billing_fields', function ($fields) {
    foreach ($fields as $key => $field) {
        $fields[$key]['required'] = true;
    }
    return $fields;
});
function save_gift_recipient_order_item_meta($item_id, $item, $order_id)
{
    if ($item->get_meta('gift_for')) {
        return;
    }
    if (isset($item->legacy_values['gift_for'])) {
        $item->add_meta_data('gift_for', $item->legacy_values['gift_for'], true);
        $item->save();
    }
}
add_action('woocommerce_new_order_item', 'save_gift_recipient_order_item_meta', 10, 3);
function custom_woocommerce_registration_redirect($redirect)
{
    if (current_user_can('seller')) {
        $redirect_url = home_url('/dashboard/');
    } else {
        $redirect_url = home_url('/wishlist/');
    }
    return $redirect_url;
}
add_filter('woocommerce_registration_redirect', 'custom_woocommerce_registration_redirect');
function custom_woocommerce_login_redirect($redirect, $user)
{
    if (!is_admin()) {
        if (in_array('seller', (array) $user->roles)) {
            $custom_redirect_url = home_url('/dashboard/');
        } else {
            $custom_redirect_url = home_url('/wishlist/');
        }
    }
    return $custom_redirect_url;
}
add_filter('woocommerce_login_redirect', 'custom_woocommerce_login_redirect', 10, 2);
// gift this product
// function add_gift_recipient_to_cart_item_data($cart_item_data, $product_id, $variation_id)
// {
//  if (isset($_POST['gift_for']) && !empty($_POST['gift_for'])) {
//      $cart_item_data['gift_for'] = sanitize_text_field($_POST['gift_for']);
//  }
//  return $cart_item_data;
// }
// add_filter('woocommerce_add_cart_item_data', 'add_gift_recipient_to_cart_item_data', 10, 3);
// 20-4-26
function add_gift_recipient_to_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    $gift_for = '';
    if (isset($_POST['gift_for']) && !empty($_POST['gift_for'])) {
        $gift_for = sanitize_text_field($_POST['gift_for']);
    } elseif (isset($_POST['_gift_for']) && !empty($_POST['_gift_for'])) {
        $gift_for = sanitize_text_field($_POST['_gift_for']);
    } elseif (isset($_GET['original_user_id']) && !empty($_GET['original_user_id'])) {
        $gift_for = sanitize_text_field($_GET['original_user_id']);
    } elseif (isset($_GET['gift_for']) && !empty($_GET['gift_for'])) {
        $gift_for = sanitize_text_field($_GET['gift_for']);
    }
    if (!empty($gift_for)) {
        $cart_item_data['gift_for'] = $gift_for;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_gift_recipient_to_cart_item_data', 10, 3);
function display_gift_recipient_in_cart($item_data, $cart_item)
{
    if (isset($cart_item['gift_for'])) {
        $user_info = get_userdata($cart_item['gift_for']);
        $first_name = get_user_meta($cart_item['gift_for'], 'first_name', true);
        $last_name = get_user_meta($cart_item['gift_for'], 'partner_fname', true);
        // var_dump($user_info);
        if ($user_info) {
            $item_data[] = array(
                'key' => __('Gift For', 'woocommerce'),
                'value' => $first_name . ' & ' . $last_name,
                'display' => '',
            );
        }
        if (isset($cart_item['custom_price'])) {
            $item_data[] = array(
                'key' => 'Custom Price',
                'value' => wc_price($cart_item['custom_price']),
                'display' => '',
            );
        }
        $hidden_value = 'custom_value';
        WC()->session->set('gift_user_id', $cart_item['gift_for']);
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_gift_recipient_in_cart', 10, 2);
// Add info icon next to Platform Fees in Cart & Checkout
add_filter('woocommerce_cart_totals_fee_html', function ($fee_html, $fee) {
    if (strpos(strtolower($fee->name), 'platform fee') !== false) {
        $info_icon = '<span class="platform-fee-tooltip"><i class="dashicons dashicons-info" title="This fee covers platform maintenance and services."></i></span>';
        $fee_html .= ' ' . $info_icon;
    }
    return $fee_html;
}, 10, 2);
// Add info icon in Order Summary on Checkout Page
add_filter('woocommerce_get_order_item_totals', function ($total_rows, $order) {
    foreach ($total_rows as $key => $row) {
        if (strpos(strtolower($row['label']), 'platform fee') !== false) {
            $info_icon = 'This fee covers platform maintenance and services.';
            $total_rows[$key]['label'] .= ' ' . $info_icon;
            $total_rows[$key]['sanitize'] = false;
        }
    }
    return $total_rows;
}, 10, 2);
// Wishlist custom profile image auto upload code
function update_user_profile_picture()
{
    // Check security nonce
    //check_ajax_referer('update_profile_picture_nonce', 'security');
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
    }
    $user_id = get_current_user_id();
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploaded_file = wp_handle_upload($_FILES['profile_picture'], ['test_form' => false]);
        if (!isset($uploaded_file['error'])) {
            $image_url = $uploaded_file['url'];
            update_user_meta($user_id, 'profile_picture', $image_url);
            wp_send_json_success(['image_url' => $image_url]);
        } else {
            wp_send_json_error(['message' => 'File upload error']);
        }
    } else {
        wp_send_json_error(['message' => 'No file uploaded']);
    }
}
add_action('wp_ajax_update_user_profile_picture', 'update_user_profile_picture');
add_action('wp_ajax_nopriv_update_user_profile_picture', 'update_user_profile_picture');
add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false');
add_filter('woocommerce_get_checkout_url', function ($url) {
    if (is_page('checkout3')) {
        return get_permalink(get_page_by_path('checkout3'));
    }
    return $url;
});
add_filter('woocommerce_get_checkout_url', function ($url) {
    if (is_page('checkout3')) {
        return get_permalink(get_page_by_path('checkout3'));
    }
    return $url;
});
function disable_woocommerce_checkout_scripts()
{
    if (is_checkout()) {
        wp_dequeue_script('wc-checkout');
    }
}
add_action('wp_enqueue_scripts', 'disable_woocommerce_checkout_scripts', 99);
add_action('wp_footer', function () {
    if (is_page('checkout3')) {
        ?>
<script>
jQuery(document).ready(function($) {
    wc_checkout_params = {
        ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
        is_checkout: true
    };
});
</script>
<?php
    }
});
add_action('wp_footer', function () {
    ?>
<script>
jQuery(document).ready(function($) {
    function disableLoaderAndEnableButton() {
        setTimeout(function() {
            $('.blockUI, .woocommerce-checkout-review-order .blockUI, .processing').hide();
            $('#place_order').prop('disabled', false);
        }, 300);
    }

    function handleAutofill() {
        setTimeout(function() {
            if (!$('.woocommerce-invalid').length) {
                disableLoaderAndEnableButton();
            }
        }, 800);
    }
    disableLoaderAndEnableButton();
    $(document).on('updated_checkout', function() {
        handleAutofill();
    });
    $('input, select').on('change', function() {
        handleAutofill();
    });
    $(document).on('change', 'input[name="payment_method"]', function() {
        disableLoaderAndEnableButton();
    });
    $(document).on('ajaxStart', function() {
        $('.blockUI, .woocommerce-checkout-review-order .blockUI, .processing').hide();
    });
    $(document).on('ajaxStop', function() {
        disableLoaderAndEnableButton();
    });
});
jQuery(document).ready(function($) {
    $('#place_order').prop('disabled', false); // Ensure button is enabled
    $('#place_order').on('click', function(e) {
        e.preventDefault(); // Prevent default button behavior
        $(this).prop('disabled', true); // Disable to prevent multiple clicks
        if ($('.woocommerce-invalid').length > 0) {
            alert("Please fill in required fields.");
            $(this).prop('disabled', false);
            return;
        }
        $('form.checkout').submit(); // Force form submission
    });
});
</script>
<?php
});
add_filter('woocommerce_checkout_process', function () {
    wc_add_notice('Processing order, please wait...', 'notice');
}, 10);
function load_wc_scripts()
{
    if (is_checkout()) {
        // echo "called";
        wp_enqueue_script('wc-checkout');
    }
}
add_action('wp_enqueue_scripts', 'load_wc_scripts');
// function process_custom_checkout()
// {
//     error_log('Function process_custom_checkout() started.');
//     if (!isset($_POST['billing_first_name']) || !isset($_POST['billing_email'])) {
//         error_log('Missing required fields: First Name or Email.');
//         wp_send_json_error(['message' => 'Missing required fields.']);
//     }
//     error_log('All required fields are present.');
//     $first_name = sanitize_text_field($_POST['billing_first_name']);
//     $last_name = sanitize_text_field($_POST['billing_last_name']);
//     $email = sanitize_email($_POST['billing_email']);
//     $phone = sanitize_text_field($_POST['billing_phone']);
//     error_log("First Name: $first_name, Email: $email");
//     // Create order
//     $order = wc_create_order();
//     if (!$order) {
//         error_log('Failed to create WooCommerce order.');
//         wp_send_json_error(['message' => 'Order creation failed.']);
//     }
//     error_log("Order created successfully. Order ID: " . $order->get_id());
//     foreach (WC()->cart->get_cart() as $cart_item) {
//         $product_id = $cart_item['product_id'];
//         $quantity = $cart_item['quantity'];
//         $order_item_id = $order->add_product(wc_get_product($product_id), $quantity);
//         error_log("Added product $product_id (Qty: $quantity) to order.");
//         if (!empty($cart_item['gift_for'])) {
//             wc_add_order_item_meta($order_item_id, '_gift_for', $cart_item['gift_for']);
//             error_log("Added gift_for meta for product ID: $product_id");
//         }
//     }
//     // Set billing and shipping details
//     $order->set_address([
//         'first_name' => $first_name,
//         'last_name'  => $last_name,
//         'email'      => $email,
//         'phone'      => $phone,
//         'address_1'  => sanitize_text_field($_POST['billing_address_1']),
//         'address_2'  => sanitize_text_field($_POST['billing_address_2']),
//         'city'       => sanitize_text_field($_POST['billing_city']),
//         'state'      => sanitize_text_field($_POST['billing_state']),
//         'postcode'   => sanitize_text_field($_POST['billing_postcode']),
//         'country'    => sanitize_text_field($_POST['billing_country']),
//     ], 'billing');
//     error_log("Billing details set.");
//     $order->set_address([
//         'first_name' => sanitize_text_field($_POST['shipping_first_name']),
//         'last_name'  => sanitize_text_field($_POST['shipping_last_name']),
//         'address_1'  => sanitize_text_field($_POST['shipping_address_1']),
//         'address_2'  => sanitize_text_field($_POST['shipping_address_2']),
//         'city'       => sanitize_text_field($_POST['shipping_city']),
//         'state'      => sanitize_text_field($_POST['shipping_state']),
//         'postcode'   => sanitize_text_field($_POST['shipping_postcode']),
//         'country'    => sanitize_text_field($_POST['shipping_country']),
//     ], 'shipping');
//     error_log("Shipping details set.");
//     $deliver_to_couple = isset($_POST['deliver_to_couple']) ? 'yes' : 'no';
//     $order->update_meta_data('_deliver_to_couple', $deliver_to_couple);
//     $order->set_customer_note(sanitize_textarea_field($_POST['order_comments']));
//     error_log("Order meta data updated.");
//     // Calculate totals and set payment status
//     $order->calculate_totals();
//     $order->set_status('processing'); 
//     $order->save();
//     error_log("Order totals calculated and status set.");
//     // Empty cart
//     WC()->cart->empty_cart();
//     error_log("Cart emptied.");
//     // Redirect to Thank You page
//     $thank_you_url = $order->get_checkout_order_received_url();
//     error_log("Redirecting to Thank You page: $thank_you_url");
//     wp_send_json_success(['redirect' => $thank_you_url]);
//     exit();
// }
function process_custom_checkout()
{
    if (!isset($_POST['billing_first_name']) || !isset($_POST['billing_email'])) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }
    $first_name = sanitize_text_field($_POST['billing_first_name']);
    $last_name = sanitize_text_field($_POST['billing_last_name']);
    $email = sanitize_email($_POST['billing_email']);
    $phone = sanitize_text_field($_POST['billing_phone']);
    $address_1 = sanitize_text_field($_POST['billing_address_1']);
    $address_2 = sanitize_text_field($_POST['billing_address_2']);
    $city = sanitize_text_field($_POST['billing_city']);
    $state = sanitize_text_field($_POST['billing_state']);
    $postcode = sanitize_text_field($_POST['billing_postcode']);
    $country = sanitize_text_field($_POST['billing_country']);
    $order_notes = sanitize_textarea_field($_POST['order_comments']);
    $deliver_to_couple = isset($_POST['deliver_to_couple']) ? 'yes' : 'no';
    // Create order
    $order = wc_create_order();
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        $order_item_id = $order->add_product(wc_get_product($product_id), $quantity);
        if (!empty($cart_item['gift_for'])) {
            wc_add_order_item_meta($order_item_id, 'gift_for', $cart_item['gift_for']);
        }
    }
    // Set billing details
    $order->set_address([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'address_1' => $address_1,
        'address_2' => $address_2,
        'city' => $city,
        'state' => $state,
        'postcode' => $postcode,
        'country' => $country,
    ], 'billing');
    // Set shipping details
    $shipping_first_name = sanitize_text_field($_POST['shipping_first_name']);
    $shipping_last_name = sanitize_text_field($_POST['shipping_last_name']);
    $shipping_address_1 = sanitize_text_field($_POST['shipping_address_1']);
    $shipping_address_2 = sanitize_text_field($_POST['shipping_address_2']);
    $shipping_city = sanitize_text_field($_POST['shipping_city']);
    $shipping_state = sanitize_text_field($_POST['shipping_state']);
    $shipping_postcode = sanitize_text_field($_POST['shipping_postcode']);
    $shipping_country = sanitize_text_field($_POST['shipping_country']);
    $order->set_address([
        'first_name' => $shipping_first_name,
        'last_name' => $shipping_last_name,
        'address_1' => $shipping_address_1,
        'address_2' => $shipping_address_2,
        'city' => $shipping_city,
        'state' => $shipping_state,
        'postcode' => $shipping_postcode,
        'country' => $shipping_country,
    ], 'shipping');
    $order->update_meta_data('_deliver_to_couple', $deliver_to_couple);
    $order->set_customer_note($order_notes);
    // Calculate totals and set payment status
    $order->calculate_totals();
    $order->set_status('processing'); // Change to 'completed' if no payment needed
    $order->save();
    //wp_schedule_single_event(time() + 10, 'send_order_email_event', [$order->get_id()]);
    WC()->cart->empty_cart();
    // Redirect to Thank You page
    ob_end_clean(); // Clear output buffer
    $thank_you_url = $order->get_checkout_order_received_url();
    wp_send_json_success(['redirect' => $thank_you_url]);
    exit();
}
add_action('wp_ajax_process_custom_checkout', 'process_custom_checkout');
add_action('wp_ajax_nopriv_process_custom_checkout', 'process_custom_checkout');
function shipping_address_filled()
{
    $user_id = get_current_user_id();
    $required_fields = [
        get_user_meta($user_id, 'shipping_street', true),
        get_user_meta($user_id, 'shipping_city', true),
        get_user_meta($user_id, 'shipping_zipcode', true),
        get_user_meta($user_id, 'shipping_country', true),
        get_user_meta($user_id, 'shipping_state', true),
    ];
    foreach ($required_fields as $field) {
        if (empty($field)) {
            wp_send_json_error(['message' => 'Missing required shipping info']);
        }
    }
    wp_send_json_success(['message' => 'All shipping fields are filled']);
}
add_action('wp_ajax_shipping_address_filled', 'shipping_address_filled');
add_action('wp_ajax_nopriv_shipping_address_filled', 'shipping_address_filled');
// add_filter('woocommerce_email_enabled_customer_processing_order', '__return_false');
// checkbok
add_action('wp_footer', function () {
    if (is_checkout() && !is_order_received_page()) {
    ?>
<script>
jQuery(document).ready(function($) {
    console.log('testedd');
    // Keep all checkout fields blank by default, except country
    function clearCheckoutFields() {
        const fields = [
            '#billing_first_name',
            '#billing_last_name',
            '#billing_company',
            '#billing_address_1',
            '#billing_address_2',
            '#billing_city',
            '#billing_postcode',
            '#billing_state',
            '#billing_phone',
            '#billing_email',
            '#shipping_first_name',
            '#shipping_last_name',
            '#shipping_company',
            '#shipping_address_1',
            '#shipping_address_2',
            '#shipping_city',
            '#shipping_postcode',
            '#shipping_state'
        ];

        $(fields.join(',')).val('').trigger('change');

        // Country fields remain unchanged
        $('#deliver_to_couple').prop('checked',true);
    }

    clearCheckoutFields();

    function showNotification(message, callback) {
        let notification = document.createElement("div");
        notification.className = "custom-notification new-custom-notification-cls-adding";
        notification.innerHTML = `
                        <div class="notification-content">
                            <p>${message}</p>
                            <button class="notification-btn">OK</button>
                        </div>
                    `;
        document.body.appendChild(notification);
        document.body.classList.add("open-modal");
        // document.querySelector(".notification-btn").addEventListener("click", function () {
        //  document.body.removeChild(notification);
        //  document.body.classList.remove("open-modal");
        //  if (callback) callback();
        // });
        // ✅ Fix here: scope the button inside this notification
        notification.querySelector(".notification-btn").addEventListener("click", function() {
            notification.remove();
            document.body.classList.remove("open-modal");
            if (callback) callback();
        });
    }
    let coupleShippingData = {};
    // Function to auto-fill shipping fields
    function autofillShippingFields(data) {
        $('#shipping_first_name').val(data.first_name);
        $('#shipping_last_name').val(data.last_name);
        $('#shipping_address_1').val(data.shipping_address_1);
        $('#shipping_city').val(data.shipping_city);
        $('#shipping_postcode').val(data.shipping_postcode);
        $('#shipping_state').val(data.shipping_state).trigger('change');
    }

    function autofillBillingFields(data) {
        $('#billing_first_name').val(data.first_name);
        $('#billing_last_name').val(data.last_name);
        $('#billing_address_1').val(data.shipping_address_1);
        $('#billing_city').val(data.shipping_city);
        $('#billing_postcode').val(data.shipping_postcode);
        $('#billing_country').val(data.shipping_country).trigger('change');
        setTimeout(function() {
            $('#billing_state').val(data.shipping_state).trigger('change');
        }, 300);
    }
    // Fetch gift recipient shipping details via AJAX
    $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        data: {
            action: 'get_gift_for_shipping'
        },
        success: function(response) {
            coupleShippingData = response.data;
            if (!coupleShippingData.first_name ||
                !coupleShippingData.last_name ||
                !coupleShippingData.shipping_address_1 ||
                !coupleShippingData.shipping_city ||
                !coupleShippingData.shipping_postcode ||
                !coupleShippingData.shipping_country ||
                !coupleShippingData.shipping_state) {
                showNotification("⚠️ Please contact the couple to fill their shipping address.");
                $('#deliver_to_couple').prop('checked', false);
                $('.shipping_address').css('display', 'block');
            } else {
                $('#deliver_to_couple').prop('checked', true);
                autofillShippingFields(coupleShippingData);
                autofillBillingFields(coupleShippingData);
            }
        }
    });
    $('#deliver_to_couple').click(function() {
        const checkbox = $(this);
        if ($(this).is(':checked')) {
            console.log("Checkbox Checked:", coupleShippingData); // Debug log
            if (coupleShippingData && Object.keys(coupleShippingData).length > 0) {
                autofillShippingFields(coupleShippingData);
                autofillBillingFields(coupleShippingData);
                const sameAsBillingCheckbox = document.querySelector("input[name='same_as_billing']");
                const sameAsBillingLabel = document.querySelector("label.same_as_billing_label");
                if (sameAsBillingCheckbox && sameAsBillingLabel) {
                    sameAsBillingCheckbox.style.display = "none";
                    sameAsBillingLabel.style.display = "none";
                    sameAsBillingCheckbox.checked = false;
                }
            } else {
                showNotification("⚠️ No saved address found for the recipient.");
                $(this).prop('checked', false);
                address_same_as_billing();
            }
        } else {
            $('.shipping_address').css('display', 'none');
            showNotificationWithTwoButton(
                "<h3>Are you sure you don’t want to ship your gifts directly to the couple’s preferred address?</h3>" +
                "<p>PS: Our newlyweds love the convenience of having items shipped directly to their doorstep!</p>",
                function() {
                    // OK clicked → Clear shipping fields
                    $('.shipping_address').css('display', 'block');
                    $('#shipping_first_name, #shipping_last_name, #shipping_address_1, #shipping_city, #shipping_postcode, #shipping_state')
                        .val('');
                    address_same_as_billing();
                },
                function() {
                    // Cancel clicked → Keep checkbox checked & autofill
                    $(this).prop('checked', true);
                    $('.shipping_address').css('display', 'none');
                    autofillShippingFields(coupleShippingData);
                    $(this).prop('checked', true);
                    checkbox.prop('checked', true);
                    const sameAsBillingCheckbox = document.querySelector(
                        "input[name='same_as_billing']");
                    const sameAsBillingLabel = document.querySelector(
                        "label.same_as_billing_label");
                    if (sameAsBillingCheckbox && sameAsBillingLabel) {
                        sameAsBillingCheckbox.style.display = "none";
                        sameAsBillingLabel.style.display = "none";
                        sameAsBillingCheckbox.checked = false;
                    }
                }
            );
        }
    });

    function address_same_as_billing() {
        const sameAsBillingCheckbox = document.querySelector("input[name='same_as_billing']");
        const sameAsBillingLabel = document.querySelector("label.same_as_billing_label");
        if (sameAsBillingCheckbox && sameAsBillingLabel) {
            sameAsBillingCheckbox.style.display = "inline-block";
            sameAsBillingLabel.style.display = "inline-block"; // also show label
            sameAsBillingCheckbox.checked = false;
        }
        const billingFirstName = document.querySelector("input[name='billing_first_name']").value;
        const billingLastName = document.querySelector("input[name='billing_last_name']").value;
        const billingAddress = document.querySelector("input[name='billing_address_1']").value;
        const billingCity = document.querySelector("input[name='billing_city']").value;
        const billingPostcode = document.querySelector("input[name='billing_postcode']").value;
        const billingState = document.querySelector("select[name='billing_state']").value;
        // Store the billing details in local storage
        const billingData = {
            billingFirstName,
            billingLastName,
            billingAddress,
            billingCity,
            billingPostcode,
            billingState
        };
        localStorage.setItem('billingData', JSON.stringify(billingData));
        if (sameAsBillingCheckbox.checked) {
            autoFillShippingDetails();
        }
    }
});
</script>

<?php

    }
});
function get_gift_for_shipping()
{
    $gift_user_data = [];
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (!empty($cart_item['gift_for'])) {
            $gift_user_id = $cart_item['gift_for'];
            $user_info = get_userdata($gift_user_id);
            $gift_user_data = [
                'first_name' => $user_info->first_name,
                'last_name' => $user_info->last_name,
                'shipping_address_1' => get_user_meta($gift_user_id, 'shipping_street', true),
                'shipping_city' => get_user_meta($gift_user_id, 'shipping_city', true),
                'shipping_postcode' => get_user_meta($gift_user_id, 'shipping_zipcode', true),
                'shipping_country' => get_user_meta($gift_user_id, 'shipping_country', true),
                'shipping_state' => get_user_meta($gift_user_id, 'shipping_state', true),
            ];
            break;
        }
        // var_dump($gift_user_data);
    }
    if (
        !empty($gift_user_data['first_name']) &&
        !empty($gift_user_data['last_name']) &&
        !empty($gift_user_data['shipping_address_1']) &&
        !empty($gift_user_data['shipping_city']) &&
        !empty($gift_user_data['shipping_postcode']) &&
        !empty($gift_user_data['shipping_country']) &&
        !empty($gift_user_data['shipping_state'])
    ) {
        wp_send_json_success($gift_user_data);
    } {
        wp_send_json_error(['message' => 'No saved shipping address.']);
    }
}
add_action('wp_ajax_get_gift_for_shipping', 'get_gift_for_shipping');
add_action('wp_ajax_nopriv_get_gift_for_shipping', 'get_gift_for_shipping');
add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin) {
        $shipping_address = $order->get_shipping_address_1() ? nl2br($order->get_formatted_shipping_address()) : 'No shipping address provided.';
        echo '<h3>' . __('Shipping Address', 'woocommerce') . '</h3>';
        echo '<address style=" text-align: left; margin-bottom:20px; padding:12px;color:#414141;border:1px solid #e5e5e5">' . $shipping_address . '</address>';
    }
}, 20, 4);
// add_action('woocommerce_checkout_order_processed', function ($order_id) {
//  $order = wc_get_order($order_id);
//  WC()->mailer()->emails['WC_Email_New_Order']->trigger($order_id);
// }, 20);
// Transfer to cash
function register_custom_order_status()
{
    register_post_status('wc-transfer', array(
        'label' => 'Transfer',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Transfer <span class="count">(%s)</span>', 'Transfer <span class="count">(%s)</span>')
    ));
    register_post_status('wc-confirm', array(
        'label' => 'Confirm',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Confirm <span class="count">(%s)</span>', 'Confirm <span class="count">(%s)</span>')
    ));
}
add_action('init', 'register_custom_order_status', 5);
/**
 * Add custom order statuses to WooCommerce order status list
 */
function add_custom_woocommerce_order_statuses($order_statuses)
{
    $custom_statuses = array(
        'wc-confirm' => _x('Confirm', 'Order status', 'woocommerce'),
        'wc-transfer' => _x('Transfer', 'Order status', 'woocommerce')
    );
    return array_merge($order_statuses, $custom_statuses);
}
add_filter('woocommerce_order_statuses', 'add_custom_woocommerce_order_statuses', 10);
/**
 * Ensure custom statuses appear in admin order list filters
 */
function add_custom_order_status_to_admin_filter($order_statuses)
{
    $order_statuses['wc-confirm'] = _x('Confirm', 'Order status', 'woocommerce');
    $order_statuses['wc-transfer'] = _x('Transfer', 'Order status', 'woocommerce');
    return $order_statuses;
}
add_filter('woocommerce_admin_order_stati', 'add_custom_order_status_to_admin_filter', 10);
/**
 * View order payment status - ensures confirm status is recognized
 */
function add_custom_status_to_order_statuses($statuses)
{
    $statuses['wc-confirm'] = _x('Confirm', 'Order status', 'woocommerce');
    $statuses['wc-transfer'] = _x('Transfer', 'Order status', 'woocommerce');
    return $statuses;
}
add_filter('wc_order_statuses', 'add_custom_status_to_order_statuses', 10);
// add_action('template_redirect', function () {
//     if (is_checkout() && !is_wc_endpoint_url('order-received')) {
//         wp_redirect(home_url()); // Redirect to homepage or custom page
//         exit;
//     }
// });
// Display the gift status in the new custom column for order items
add_action('woocommerce_admin_order_item_values', 'display_gift_status_in_column', 10, 3);
function display_gift_status_in_column($item_id, $item, $order)
{
    if (is_array($item_id) && isset($item_id['id'])) {
        $order_item_id = $item->get_id();
    } else {
        $order_item_id = $item->get_id();
    }
    $gift_status = wc_get_order_item_meta($order_item_id, '_gift_status', true);
    if (empty($gift_status)) {
        $gift_status = 'Pending';
    }
    echo '<td class="gift-status-column">' . esc_html($gift_status) . '</td>';
}
add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_main_query() && is_tax('product_cat')) {
        if (isset($_GET['brand']) && !empty($_GET['brand'])) {
            $brand_slug = sanitize_text_field($_GET['brand']);
            $tax_query = $query->get('tax_query');
            $tax_query[] = array(
                'taxonomy' => 'brand', // Make sure this is your correct taxonomy slug
                'field' => 'slug',
                'terms' => $brand_slug,
            );
            $query->set('tax_query', $tax_query);
        }
    }
});
// Handle the AJAX request to add product to cart with variations and quantity
add_action('wp_ajax_add_to_cart_with_variations', 'add_to_cart_with_variations');
add_action('wp_ajax_nopriv_add_to_cart_with_variations', 'add_to_cart_with_variations');
function add_to_cart_with_variations()
{
    if (isset($_POST['product_id'], $_POST['quantity'])) {
        $product_id = intval($_POST['product_id']);
        $attributes = isset($_POST['attributes']) ? json_decode(stripslashes($_POST['attributes']), true) : [];
        $quantity = intval($_POST['quantity']);
        $gift_for = isset($_POST['gift_for']) ? sanitize_text_field($_POST['gift_for']) : '';
        $custom_amount = isset($_POST['custom_amount']) ? floatval($_POST['custom_amount']) : 0;
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => 'Invalid product.'));
        }
        $variation_id = 0;
        $cart_item_data = [];
        if (!empty($gift_for)) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if (isset($cart_item['gift_for']) && $cart_item['gift_for'] !== $gift_for) {
                    wp_send_json_error(array('message' => 'You can gift to only one couple at a time.'));
                }
            }
            $cart_item_data['gift_for'] = $gift_for;
        }
        // Handle custom price (for Lifafa/cash product)
        if ($custom_amount > 0) {
            $cart_item_data['custom_price'] = $custom_amount;
            $variation_data = ['Custom amount' => wc_price($custom_amount)];
            $cart_item_data['variation'] = $variation_data;
        } else {
            // Use variation matching if no custom price
            if ($product->is_type('variable')) {
                $variation_id = find_matching_variation_id($product, $attributes);
                $cart_item_data['variation'] = $attributes;
            }
        }
        // Check if product already in cart
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (
                $cart_item['product_id'] == $product_id &&
                $cart_item['variation_id'] == $variation_id &&
                (isset($cart_item['gift_for']) ? $cart_item['gift_for'] : '') === $gift_for &&
                $cart_item['variation'] == $cart_item_data['variation']
            ) {
                WC()->cart->set_quantity($cart_item_key, $quantity);
                wp_send_json_success(array('message' => 'Product quantity updated in cart.'));
            }
        }
        // Add to cart
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, [], $cart_item_data);
        wp_send_json_success(array('message' => 'Product added to cart.'));
    } else {
        wp_send_json_error(array('message' => 'Missing required fields.'));
    }
}
// Function to find matching variation ID
function find_matching_variation_id($product, $attributes)
{
    $available_variations = $product->get_available_variations();
    foreach ($available_variations as $variation) {
        $match = true;
        foreach ($attributes as $attribute_name => $attribute_value) {
            if (isset($variation['attributes'][$attribute_name]) && $variation['attributes'][$attribute_name] !== $attribute_value) {
                $match = false;
                break;
            }
        }
        if ($match) {
            return $variation['variation_id'];
        }
    }
    return false;
}
add_action('woocommerce_cleanup_sessions', function () {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_wc_session_%'");
});
add_filter('woocommerce_billing_fields', function ($fields) {
    unset($fields['billing_company']);
    return $fields;
});
add_filter('woocommerce_checkout_fields', function ($fields) {
    // Remove required attribute from phone number
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['required'] = false;
    }
    return $fields;
});
add_filter('woocommerce_checkout_fields', function ($fields) {
    $fields['billing']['billing_email']['priority'] = 1;
    $fields['billing']['billing_first_name']['priority'] = 10;
    $fields['billing']['billing_last_name']['priority'] = 20;
    $fields['billing']['billing_phone']['priority'] = 30;
    $fields['billing']['billing_address_1']['priority'] = 50;
    $fields['billing']['billing_address_2']['priority'] = 60;
    $fields['billing']['billing_city']['priority'] = 70;
    $fields['billing']['billing_postcode']['priority'] = 80;
    $fields['billing']['billing_country']['priority'] = 40;
    $fields['billing']['billing_state']['priority'] = 90;
    return $fields;
});
add_filter('woocommerce_checkout_fields', 'remove_company_name_from_shipping');
function remove_company_name_from_shipping($fields)
{
    unset($fields['shipping']['shipping_company']); // Remove Company Name from Shipping
    return $fields;
}
// add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity) {
//     if (!isset($_GET['_gift_for']) || empty($_GET['_gift_for'])) {
//         wc_add_notice(__('No couple selected for this gift.'), 'error');
//         return false; 
//     }
//     return $passed;
// }, 10, 3);
function redirect_logged_in_users_from_signup()
{
    if (is_page('sign-up') && is_user_logged_in()) {
        wp_redirect(home_url('/modernlifafa-elementor/wishlist/'));
        exit;
    }
}
add_action('template_redirect', 'redirect_logged_in_users_from_signup');
function redirect_guest_users_from_wishlist()
{
    if (is_page('wishlist')) {
        // if (!is_user_logged_in()) {
        //     wp_redirect(home_url());
        //     exit;
        // }
        if (isset($_GET['original_user_id'])) {
            $original_user_id = intval($_GET['original_user_id']);
            if (!get_user_by('ID', $original_user_id)) {
                setcookie('wishlist_error', 'No user exists with this ID.', time() + 10, "/");
                wp_redirect(home_url());
                exit;
            }
        }
    }
}
add_action('template_redirect', 'redirect_guest_users_from_wishlist');
function display_toastr_notification()
{
    if (isset($_COOKIE['wishlist_error'])) {
    ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    toastr.error("<?php echo esc_js($_COOKIE['wishlist_error']); ?>");
});
</script>
<?php
        setcookie('wishlist_error', '', time() - 3600, "/"); // Clear the cookie
    }
}
add_action('wp_footer', 'display_toastr_notification');
// function redirect_to_cart_after_order($order_id, $posted_data, $order)
// {
//  if (!is_admin()) {
//      wc_clear_cart(); // Clear the cart after order placement
//      wp_safe_redirect(wc_get_cart_url()); // Redirect to the cart page
//      exit;
//  }
// }
// add_action('woocommerce_checkout_order_processed', 'redirect_to_cart_after_order', 10, 3);
// FAQ Custom Post Type
function create_posttype()
{
    register_post_type(
        'FAQ',
        array(
            'labels' => array(
                'name' => __('FAQ'),
                'singular_name' => __('FAQ')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'faq'),
            'show_in_rest' => true,
            'taxonomies' => array('category'),
        )
    );
}
add_action('init', 'create_posttype');
function custom_password_reset_message($message, $key, $user_login, $user_data)
{
    return __('A password reset email has been sent to the registered email address. It may take few minutes to show up in your inbox.', 'text-domain');
}
add_filter('password_reset_messages', 'custom_password_reset_message', 10, 4);
function custom_reset_password_expiration($expiration)
{
    return 3600; // 3600 seconds = 1 hour
}
add_filter('password_reset_expiration', 'custom_reset_password_expiration');
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $deliver_to_couple = $order->get_meta('_deliver_to_couple');
    if ($deliver_to_couple) {
        echo '<p><strong>Deliver to Couple:</strong> ' . esc_html($deliver_to_couple) . '</p>';
    }
});
add_action('woocommerce_thankyou', function ($order_id) {
    if (!$order_id)
        return;
    $order = wc_get_order($order_id);
    $deliver_to_couple = $order->get_meta('_deliver_to_couple');
    if ($deliver_to_couple) {
        echo '<p><strong>Deliver to Couple:</strong> ' . esc_html($deliver_to_couple) . '</p>';
    }
});
add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin && $email->id === 'new_order') {
        $deliver_to_couple = $order->get_meta('_deliver_to_couple');
        if ($deliver_to_couple) {
            echo '<p><strong>Deliver to Couple:</strong> ' . esc_html($deliver_to_couple) . '</p>';
        }
    }
}, 20, 4);
add_action('template_redirect', function () {
    if (is_checkout() && !is_wc_endpoint_url('order-received')) {
        if (WC()->cart->is_empty()) {
            wp_redirect(wc_get_cart_url());
            exit;
        }
    }
});
add_filter('http_request_timeout', function () {
    return 60; // Increase to 60 seconds
});
add_filter('http_request_host_is_external', function ($allow, $host) {
    if (strpos($host, 'razorpay.com') !== false) {
        return true;
    }
    return $allow;
}, 10, 2);
add_action('woocommerce_thankyou', 'display_gift_message_on_thankyou', 20);
function display_gift_message_on_thankyou($order_id)
{
    $gift_message = get_post_meta($order_id, 'gift_message', true);
    if (!empty($gift_message)) {
        echo '<h2>' . __('Gift Message') . '</h2>';
        echo '<p>' . nl2br(esc_html($gift_message)) . '</p>';
    }
}
add_filter('woocommerce_order_item_get_formatted_meta_data', 'remove_vendor_name_from_order_meta', 10, 2);
function remove_vendor_name_from_order_meta($formatted_meta, $order_item)
{
    foreach ($formatted_meta as $key => $meta) {
        if (isset($meta->key) && $meta->key === 'Vendor') {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}
add_filter('woocommerce_order_item_get_formatted_meta_data', 'replace_gift_for_id_with_name', 20, 2);
function replace_gift_for_id_with_name($formatted_meta, $order_item)
{
    foreach ($formatted_meta as $key => $meta) {
        if ($meta->key === 'gift_for') {
            $user_id = $meta->value;
            $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            if ($first_name || $last_name) {
                $full_name = trim($first_name . ' ' . $last_name);
                $meta->value = $full_name;
            } else {
                $meta->value = __('User not found');
            }
            $formatted_meta[$key] = $meta;
        }
    }
    return $formatted_meta;
}
add_action('init', function () {
    remove_action('woocommerce_after_cart_item_name', 'dokan_product_seller_info', 10);
    remove_action('woocommerce_after_shop_loop_item_title', 'dokan_show_sold_by_on_the_shop_page', 9);
});
//add_action('woocommerce_order_item_meta_end', 'display_custom_order_item_meta_in_email', 10, 4);
// add_action('woocommerce_thankyou', 'custom_thank_you_message', 20);
// function custom_thank_you_message($order_id) {
//     if (!$order_id) return;
//     $order = wc_get_order($order_id);
//     echo 'Thank you for choosing Modern Lifafa — The couple will be notified of your gift along with your personal message, so they can feel the love right away. 💌 We’ve also sent a confirmation email with all the details to your inbox.';
// }
// Make "Ship to a different address?" checkbox UNCHECKED by default
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
// Copy billing address to shipping when checkbox is unchecked
add_action('woocommerce_checkout_process', 'auto_copy_billing_to_shipping');
function auto_copy_billing_to_shipping()
{
    if (empty($_POST['ship_to_different_address'])) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'billing_') === 0) {
                $shipping_key = str_replace('billing_', 'shipping_', $key);
                $_POST[$shipping_key] = $value;
            }
        }
    }
}
function custom_woocommerce_order_received_subject($subject, $order)
{
    $subject = 'Thanks for your order!';
    return $subject;
}
add_filter('gettext', 'change_empty_cart_message', 20, 3);
function change_empty_cart_message($translated, $text, $domain)
{
    if (strpos($text, 'No products in the cart') !== false) {
        return 'Looks like your cart is empty, keep browsing for the perfect gift!';
    }
    return $translated;
}
// Custom password error message
function custom_password_error_message($user, $password)
{
    if (!wp_check_password($password, $user->user_pass, $user->ID)) {
        return new WP_Error(
            'incorrect_password',
            sprintf(
                __('Hmm, that password didn’t match. Try again or <a href="%s">reset your password here</a> if you need a little help!'),
                wp_lostpassword_url()
            )
        );
    }
    return $user;
}
add_filter('wp_authenticate_user', 'custom_password_error_message', 10, 2);
function custom_email_exists_error_message($message, $email)
{
    $login_url = home_url('/my-account/');
    return __('Looks like you already have an account with us—just ', 'woocommerce') .
        '<a href="' . esc_url($login_url) . '">log in</a>' .
        __(' to pick up where you left off!', 'woocommerce');
}
add_filter('woocommerce_registration_error_email_exists', 'custom_email_exists_error_message', 10, 2);
// Hook to handle the AJAX request
add_action('wp_ajax_update_personalized_product_quantity', 'update_personalized_product_quantity');
add_action('wp_ajax_nopriv_update_personalized_product_quantity', 'update_personalized_product_quantity');
function update_personalized_product_quantity()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'User not logged in.']);
    }
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    $raw_attributes = isset($_POST['attributes']) ? $_POST['attributes'] : '';
    if ($raw_attributes === '""' || $raw_attributes === '') {
        $saved_attributes = null;
    } else {
        $decoded = json_decode(stripslashes($raw_attributes), true);
        if (is_array($decoded)) {
            $saved_attributes = wc_clean($decoded);
        } else {
            $saved_attributes = null;
        }
    }
    $personalized_products = get_user_meta($user_id, '_personalized_products', true);
    if (!is_array($personalized_products))
        $personalized_products = [];
    $product_qty = get_user_meta($user_id, '_product_quantity', true);
    if (!is_array($product_qty))
        $product_qty = [];
    $matched = false;
    $item_variation_id = '';
    // if($saved_attributes){
    foreach ($personalized_products as &$item) {
        $item_attrs = isset($item['attributes']) ? wc_clean((array) $item['attributes']) : [];
        ksort($item_attrs);
        $item_variation_id = isset($item['variation_id']) ? intval($item['variation_id']) : 0;
        $is_match = intval($item['product_id']) === $product_id;
        if ($saved_attributes !== null) {
            $is_match = $is_match && ($item_attrs === $saved_attributes);
        }
        if ($is_match) {
            $item['quantity'] = $qty;
            $key = $variation_id ? ($product_id . '_' . $variation_id) : $product_id;
            $product_qty[$key] = $qty;
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        wp_send_json_error(['message' => 'Matching personalized product not found.']);
    }
    // }
    update_user_meta($user_id, '_personalized_products', $personalized_products);
    update_user_meta($user_id, '_product_quantity', $product_qty);
    wp_send_json_success(['message' => 'Product quantity updated.']);
}
// add_action('wp_ajax_update_personalized_product_quantity', 'update_personalized_product_quantity');
function cf7_success_popup_markup()
{
    if (is_page('contact-us') || is_page('business-enquiries')) { // Change to your actual page slug
    ?>
<div id="cf7-success-popup" class="new-success-popup-cls-adding all-popup-cls-typing-add" style="display: none;">
    <div class="popup-content">
        <div class="new-popup-inner-cls">
            <div class="new-inside-taking-border new-custom-all-type-cls-got">
                <div class="popup-message new-all-msg-cls-adding">
                    <div class="share-wishlist-address">
                        <p>Thanks for dropping by! We’ve got your message and will be in touch soon.</p>
                    </div>
                    <button class="notification-btn" id="popup-close">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('wpcf7mailsent', function() {
        const popup = document.getElementById('cf7-success-popup');
        if (popup) {
            popup.style.display = 'block';
        }
    });
    const closeBtn = document.getElementById('popup-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            const popup = document.getElementById('cf7-success-popup');
            if (popup) {
                popup.style.display = 'none';
            }
        });
    }
});
</script>
<?php
    }
}
add_action('wp_footer', 'cf7_success_popup_markup');
function custom_indian_price_format($formatted_price, $price, $args)
{
    $formatted_number = indian_number_format($price);
    return $formatted_number;
}
function indian_number_format($number)
{
    $decimal = '';
    if (strpos($number, '.') !== false) {
        list($number, $decimal) = explode('.', $number);
        $decimal = '.' . substr($decimal, 0, 2); // limit to 2 decimal places
    }
    $length = strlen($number);
    if ($length > 3) {
        $last3 = substr($number, -3);
        $restUnits = substr($number, 0, $length - 3);
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        return $restUnits . ',' . $last3 . $decimal;
    } else {
        return $number . $decimal;
    }
}
add_filter('formatted_woocommerce_price', 'custom_indian_price_format', 10, 3);
add_action('woocommerce_checkout_process', function () {
    if (!empty($_POST['gift_message']) && strlen($_POST['gift_message']) > 150) {
        wc_add_notice(__('Gift message must be 150 characters or less.', 'woocommerce'), 'error');
    }
    if (!empty($_POST['order_comments']) && strlen($_POST['order_comments']) > 150) {
        wc_add_notice(__('Order note must be 150 characters or less.', 'woocommerce'), 'error');
    }
});
function characters_limit()
{
    ?>
<div id="characters_limit" style="display: none;">
    <div class="popup-overlay"
        style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.5); z-index:9998;">
    </div>
    <div class="popup-content"
        style="position: fixed; top:50%; left:50%; transform: translate(-50%, -50%); background:#fff; padding: 20px 30px; border-radius: 8px; z-index:9999;">
        <div class="custom-notification new-custom-all-type-cls-adding">
            <div class="notification-content">
                <div class="message">
                    <p></p>
                    <div class="share-wishlist-address">
                        <p></p>
                    </div>
                    <p></p>
                </div>
                <button class="notification-btn" id="popup-close">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const limitFields = ['gift_message', 'order_comments'];

    function showPopup(message) {
        const popup = document.getElementById('characters_limit');
        const messageEl = popup.querySelector('.message p'); // Target the <p> tag for message
        if (popup && messageEl) {
            messageEl.textContent = message;
            popup.style.display = 'block';
        }
    }
    limitFields.forEach(id => {
        const textarea = document.getElementById(id);
        if (textarea) {
            textarea.addEventListener('input', function() {
                if (this.value.length > 150) {
                    this.value = this.value.substring(0, 150);
                    showPopup("Oops! Please keep your message under 150 characters.");
                }
            });
        }
    });
    const form = document.getElementById('custom-checkout-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            limitFields.forEach(id => {
                const field = document.getElementById(id);
                if (field && field.value.length > 150) {
                    showPopup(
                        `The "${id.replace('_', ' ')}" field cannot exceed 150 characters.`);
                    isValid = false;
                }
            });
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    const closeBtn = document.getElementById('popup-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            const popup = document.getElementById('characters_limit');
            if (popup) {
                popup.style.display = 'none';
            }
        });
    }
});
</script>
<?php
}
add_action('wp_footer', 'characters_limit');
// Charecter limit in product name wordpress backend
function limit_product_title_length_admin()
{
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'product') {
    ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const titleField = document.getElementById('title');
    if (titleField) {
        titleField.addEventListener('input', function() {
            if (this.value.length > 150) {
                this.value = this.value.substring(0, 150);
                alert("Product name cannot exceed 150 characters.");
            }
        });
    }
});
</script>
<?php
    }
}
add_action('admin_footer', 'limit_product_title_length_admin');
// Add shortcode in cf7 for current year in mail
// Shortcode to return current year
function cf7_current_year_shortcode()
{
    return date("Y");
}
add_shortcode('current_year', 'cf7_current_year_shortcode');
// Allow shortcodes to run in CF7 mail templates
add_filter('wpcf7_special_mail_tags', function ($output, $name) {
    if ($name === 'current_year') {
        return date('Y');
    }
    return $output;
}, 10, 2);
// 06-05-2025
add_action('woocommerce_before_calculate_totals', 'set_custom_price_for_cart_items', 20, 1);
function set_custom_price_for_cart_items($cart)
{
    if (is_admin() && !defined('DOING_AJAX'))
        return;
    // Only run once
    if (did_action('woocommerce_before_calculate_totals') >= 2)
        return;
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price(floatval($cart_item['custom_price']));
        }
    }
}
add_action('woocommerce_checkout_order_processed', 'defer_email_sending', 10, 1);
function defer_email_sending($order_id)
{
    if (function_exists('as_schedule_single_action')) {
        as_schedule_single_action(time() + 10, 'send_order_emails_later', array($order_id));
    }
}
add_action('send_order_emails_later', 'send_emails_background');
function send_emails_background($order_id)
{
    WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
}
// Disable built-in WooCommerce emails
add_filter('woocommerce_email_enabled_new_order', '__return_false');
add_filter('woocommerce_email_enabled_customer_processing_order', '__return_false');
add_filter('woocommerce_email_enabled_customer_invoice', '__return_false');
add_filter('woocommerce_email_enabled_customer_completed_order', '__return_false');
// Step 1: Defer emails to background
add_action('woocommerce_checkout_order_processed', 'defer_all_emails_in_background', 10, 1);
function defer_all_emails_in_background($order_id)
{
    if (function_exists('as_schedule_single_action')) {
        as_schedule_single_action(time() + 5, 'handle_wc_email_queue', array('order_id' => $order_id));
    }
}
// Step 2: Background email sender
add_action('handle_wc_email_queue', 'send_all_emails_background');
function send_all_emails_background($args)
{
    if (empty($args['order_id']))
        return;
    $order_id = $args['order_id'];
    $mailer = WC()->mailer();
    // List of emails to trigger manually
    $emails = [
        'WC_Email_New_Order',
        'WC_Email_Customer_Processing_Order',
        'WC_Email_Customer_Invoice',
        'WC_Email_Customer_Completed_Order',
    ];
    foreach ($emails as $email_key) {
        if (!empty($mailer->emails[$email_key])) {
            $mailer->emails[$email_key]->trigger($order_id);
        }
    }
}
// Manual Product Popup HTML Shortcode
function manual_product_popup_shortcode()
{
    ob_start(); ?>
<!-- ===== Manual Product Popup ===== -->
<div id="manualProductPopup" class="custom-popup add-manual-product">
    <div class="popup-overlay"></div>
    <div class="popup-box">
        <div class="inner-popu-box-cls-adding">
            <!-- <button class="close-popup">✕</button> -->
            <h2 class="popup-title">Add Gift Manually</h2>
            <form id="manual_fields" class="form purchased-popup-form-cls-adding">
                <div class="form-content">
                    <div class="left-fields">
                        <div class="form-group">
                            <input type="text" id="manual_product_name" placeholder="Enter Product Name" name="name"
                                required>
                        </div>
                        <div class="form-group">
                            <input type="text" id="manual_product_price" placeholder="Enter Product Price"
                                name="amazon_price">
                        </div>
                        <div class="form-group">
                            <input type="text" id="manual_product_share_url" placeholder="Paste Product URL"
                                name="amazon_url">
                        </div>
                    </div>
                    <div class="right-upload">
                        <label for="manual_product_image" class="upload-box">
                            <div class="grid-top-all-cls-adding">
                                <div class="upload-icon">
                                    <!DOCTYPE svg
                                        PUBLIC "-//W3C//DTD SVG 20010904//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
                                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="130px" height="130px"
                                        viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">
                                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                            fill="#ffc4d8" stroke="none">
                                            <path d="M790 4904 c-89 -14 -145 -31 -220 -66 -120 -58 -231 -168 -288 -288
                                -72 -152 -67 -6 -67 -1990 0 -1984 -5 -1837 67 -1990 57 -119 169 -231 289
                                -288 151 -72 6 -67 1989 -67 1984 0 1837 -5 1990 67 119 57 231 169 288 289
                                72 151 67 6 67 1989 0 1983 5 1838 -67 1989 -54 114 -169 231 -278 285 -157
                                77 21 70 -1965 72 -979 1 -1791 0 -1805 -2z m3661 -256 c93 -46 158 -112 202
                                -206 l32 -67 3 -1214 2 -1214 -32 25 c-310 231 -1031 745 -1075 765 -83 39
                                -212 44 -303 13 -75 -25 -93 -38 -685 -469 -242 -176 -456 -328 -475 -337 -48
                                -24 -117 -29 -165 -12 -22 8 -159 93 -305 190 -152 101 -291 185 -325 198 -79
                                30 -205 32 -290 5 -42 -14 -138 -71 -328 -197 -147 -98 -270 -178 -272 -178
                                -3 0 -4 546 -3 1213 l3 1212 32 67 c53 113 138 188 258 230 47 16 163 17 1851
                                15 l1800 -2 75 -37z m-949 -2111 c25 -12 303 -209 617 -437 l571 -415 -2 -470
                                -3 -470 -32 -67 c-44 -94 -109 -160 -202 -206 l-75 -37 -1816 0 -1816 0 -75
                                37 c-93 46 -158 112 -202 206 l-32 67 -3 471 -3 470 318 212 c175 116 331 216
                                346 222 32 11 99 13 147 4 18 -4 159 -91 315 -194 323 -214 350 -226 499 -218
                                73 4 95 10 158 41 40 20 237 156 438 303 580 423 667 485 697 494 50 15 107
                                10 155 -13z" />
                                            <path d="M1404 3615 c-82 -18 -137 -47 -201 -107 -90 -86 -133 -185 -133 -308
                                0 -124 43 -222 135 -309 168 -159 422 -155 588 10 168 168 168 430 0 598 -65
                                64 -139 104 -227 120 -72 13 -84 13 -162 -4z m174 -220 c152 -64 172 -273 34
                                -369 -57 -40 -149 -48 -208 -18 -165 85 -164 298 1 385 39 21 125 21 173 2z" />
                                        </g>
                                    </svg>
                                </div>
                                <p>UPLOAD IMAGE</p>
                            </div>
                        </label>
                        <input type="file" id="manual_product_image" name="amazon_image" accept="image/*" hidden>
                        <!-- <span id="file_name_display" class="file_name_display"></span> -->
                    </div>
                </div>
                <div class="button-wrap">
                    <button type="button" id="save_manual_product" class="custom_button">ADD GIFT</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
    return ob_get_clean();
}
add_shortcode('manual_product_popup', 'manual_product_popup_shortcode');
// Create custom table for mark as purchased submissions
function create_purchased_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'purchased_submissions';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NULL,
        product_id BIGINT(20) UNSIGNED NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        contact VARCHAR(100) NOT NULL,
        message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    // $args = [
    //  'post_type' => 'amazon_product',
    //  'post_status' => 'publish',
    //  'meta_query' => [
    //      'relation' => 'AND',
    //      [
    //          'key' => 'user_id',
    //          'value' => $user_id,
    //          'compare' => '='
    //      ],
    //      [
    //          'key' => 'product_id',
    //          'value' => $product_id,
    //          'compare' => '='
    //      ]
    //  ],
    //  'fields' => 'ids',
    //  'posts_per_page' => 1,
    // ];
    // $query = new WP_Query($args);
    // if (!empty($query->posts)) {
    //  $amazon_post_id = $query->posts[0]; // the amazon_product post ID
    //  update_post_meta($amazon_post_id, 'purchased', 'yes');
    //  return $amazon_post_id;
    // }
    return false;
}
add_action('after_setup_theme', 'create_purchased_table');
// Three button new pop up 3-11-25
// ============================
// 💝 Registry Popup Shortcode
// ============================
function registry_popup_shortcode()
{
    ob_start(); ?>
<div class="registry-popup">
    <div class="registry-popup-inner">
        <div class="popup-header">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/three_btn_popup.png'); ?>"
                alt="Gift Icon" class="popup-icon" />
            <h2>Your Registry, Your Way</h2>
        </div>
        <div class="register-auto-scroll">
            <!-- ============================
                     1. Modern Lifafa Gifts
                ============================= -->
            <div class="registry-section">
                <div class="new-all-btn-cls-adding">
                    <div class="add-gift-from-modern-lifafa mt-0">
                        <h3>
                            Gifts from Modern
                            <span>Lifafa</span>
                        </h3>
                        <p>
                            <!-- Browse our Curated Collections for couple favourites -->
                             Explore our curated collections of couple favourites.
                        </p>
                    </div>
                    <div class="browse-gifts-button-wrap">
                        <a href="<?php echo esc_url(home_url('/products/')); ?>" class="popup-btn">
                            Browse Gifts
                        </a>
                    </div>
                </div>
            </div>
            <!-- ============================
                     2. Cash Funds
                ============================= -->
            <div class="registry-section">
                <div class="new-all-btn-cls-adding">
                    <div class="add-gift-from-modern-lifafa">
                        <h3>Cash Funds</h3>
                        <p>
                            <!-- Add Cash Funds like ‘Honeymoon’ or ‘Date Night’ for future memories -->
                            Create a fund for your honeymoon, first home or date nights, and let guests chip in.
                        </p>
                    </div>
                    <div class="browse-gifts-button-wrap">
                        <a href="<?php echo esc_url(home_url('/products/?category=cash')); ?>" class="popup-btn">
                           Add a Fund
                        </a>
                    </div>
                </div>
            </div>
            <!-- ============================
                     3. External Store Product
                ============================= -->
            <div class="registry-section">
                <div class="new-all-btn-cls-adding">
                    <div class="add-gift-from-modern-lifafa">
                        <h3>Gifts from Any Store</h3>
                        <p>
                            <!-- Something else on your mind? Paste a product URL from any online store below. -->
                            Found something you love elsewhere? Paste the product link below.
                        </p>
                        <div class="amazon-input">
                            <!--
                                    IMPORTANT:
                                    Keep this ID unchanged because your
                                    existing JS uses amazon_product_url.
                                -->
                            <input type="url" id="amazon_product_url" class="amazon-url-input-cls"
                                placeholder="Paste Product link here" autocomplete="off" />
                        </div>
                        <small class="external-store-help">
                            Works with Amazon, Flipkart, Myntra, Nykaa and most online stores.
                        </small>
                    </div>
                    <div class="browse-gifts-button-wrap">
                        <!--
                                IMPORTANT:
                                Keep this ID unchanged because your
                                existing JS/AJAX uses fetch_amazon_url.
                            -->
                        <button type="button" id="fetch_amazon_url" class="add-url-btn popup-btn" disabled>
                            ADD TO REGISTRY
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    return ob_get_clean();
}
add_shortcode(
    'registry_popup',
    'registry_popup_shortcode'
);
// Amazon product add success pop up 
add_action('wp_footer', function () { ?>
<div class="success-popup new-success-popup-cls-adding" style="display:none;">
    <div class="popup-content">
        <div class="new-popup-inner-cls">
            <div class="new-inside-taking-border">
                <button class="close-popup">
                    <svg width="48px" height="48px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                            fill="#cbb04b" />
                    </svg>
                </button>
                <div class="popup-icon"><img
                        src="<?php echo get_template_directory_uri() . '/assets/images/gift_icon.png'; ?>" alt="">
                </div>
                <h2 class="popup-title">It's on Your List!</h2>
                <p class="popup-message"></p>
                <button class="popup-btn">View Registry</button>
                <p class="popup-note no-underline">
                    Please Note: Gifts from third party links are purchased directly<br /> on the external site and
                    shipped to the Guest.
                </p>
            </div>
        </div>
    </div>
</div>
<?php });
// Amazon product Fail pop up 
add_action('wp_footer', function () { ?>
<div class="fail-popup amazon-fail-popup" style="display:none;">
    <div class="popup-content oops-something-went-wrong-cls">
        <div class="new-popup-inner-cls">
            <div class="new-inside-taking-border">
                <button class="close-popup">
                    <svg width="48px" height="48px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                            fill="#cbb04b" />
                    </svg>
                </button>
                <div class="popup-icon">
                    <img class="broken-link-cls-adding-here"
                        src="<?php echo get_template_directory_uri() . '/assets/images/Broken_Link.png'; ?>" alt="">
                </div>
                <h2 class="popup-title">Oops! Something Went Wrong</h2>
                <p class="popup-message"></p>
                <button class="popup-btn">TRY AGAIN WITH URL</button>
                <a href="">
                    <p class="popup-note manual-try-again">
                        Try Manual Option
                    </p>
                </a>
            </div>
        </div>
    </div>
</div>
<?php });
// Amazon product already exists in registry pop up 
add_action('wp_footer', function () { ?>
<div class="fail-popup amazon-exists-popup" style="display:none;">
    <div class="popup-content oops-something-went-wrong-cls">
        <div class="new-popup-inner-cls">
            <div class="new-inside-taking-border">
                <button class="close-popup">
                    <svg width="48px" height="48px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                            fill="#cbb04b" />
                    </svg>
                </button>
                <div class="popup-icon">
                    <img class="broken-link-cls-adding-here"
                        src="<?php echo get_template_directory_uri() . '/assets/images/Broken_Link.png'; ?>" alt="">
                </div>
                <h2 class="popup-title">Oops! Something Went Wrong</h2>
                <p class="popup-message"></p>
                <button class="popup-btn">Go to My Registry</button>
                <a href="<?php echo home_url('/products'); ?>">
                    <p class="popup-note manual-try-again">
                        Check Product Listing
                    </p>
                </a>
            </div>
        </div>
    </div>
</div>
<?php });
// Add to cart pop up success and fail
add_action('wp_footer', function () { ?>
<div class="cart-success-popup new-success-popup-cls-adding all-popup-cls-typing-add" id="add-to-cart-success-popup"
    style="display:none;">
    <div class="popup-content">
        <div class="new-popup-inner-cls">
            <div class="new-inside-taking-border">
                <!--                    <button  class="close-popup"> -->
                <svg class="remove-close-popup close-popup" width="48px" height="48px" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                        fill="#cbb04b" />
                </svg>
                <!--                    </button> -->
                <div class="popup-icon"><img
                        src="<?php echo get_template_directory_uri() . '/assets/images/gift_icon.png'; ?>" alt="">
                </div>
                <!-- <h2 class="popup-title"></h2> -->
                <p class="popup-message new-all-msg-cls-adding"></p>
            </div>
        </div>
    </div>
</div>
<?php });
// After mark as purchasd successfully
add_action('wp_footer', function () { ?>
<!-- ===== Custom Success Popup ===== -->
<div id="purchasedSuccessPopup" class="purchased-success-popup notified-notification-cls-adding" style="display:none;">
    <div class="popup-overlay"></div>
    <div class="popup-box notified-notification-cls-adding-popup-box">
        <div class="new-couple-will-notified-cls">
            <div class="new-couple-set-notified-cls">
                <div class="new-inside-taking-border-added-all">
                    <button type="button" class="close-popup">
                        <svg width="48px" height="48px" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                                fill="#cbb04b" />
                        </svg>
                    </button>
                    <div class="popup-icon">
                        <img class="broken-link-cls-adding-here"
                            src="<?php echo get_template_directory_uri() . '/assets/images/mark-purchased-succes-bird.png'; ?>"
                            alt="mark-as-purchased-success-img">
                    </div>
                    <h2 class="popup-title">All Set!</h2>
                    <p class="popup-message">
                        Your gift has been marked as purchased and the couple will be<br> notified.
                        Thank you for celebrating with them!
                    </p>
                    <div class="button-wrap">
                        <button class="popup-btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php });
function enqueue_custom_scripts()
{
    wp_enqueue_script('modern_lifafa-custom-js', get_template_directory_uri() . '/js/custom.js', array('jquery'), null, true);
    wp_localize_script('modern_lifafa-custom-js', 'MyAjax', array(
        'wishlistUrl' => site_url('/wishlist')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
// End  amazon product add success pop up 
// Mark as purchased pop up shortcode
function mark_as_purchased_shortcode()
{
    ob_start();
?>
<!-- Popup Form (mark as purchased)-->
<div class="mark-as-puchased">
    <div class="amazon-popup-content">
        <div class="popup-content">
            <div class="new-popup-inner-cls-typed">
                <div class="new-inside-taking-border-added-all">
                    <div class="popup-icon"><img
                            src="<?php echo get_template_directory_uri() . '/assets/images/gift_icon.png'; ?>" alt="">
                    </div>
                    <h2 class="popup-title marked-gift-purchased-cls">Mark Gift As Purchased</h2>
                    <p class="popup-title new-purchased-title-cls-bought">Bought it? Let the couple know their gift has
                        been chosen and help keep their
                        registry up to date.</p>
                    <div id="purchased-popup">
                        <form id="purchasedForm" class="form">
                            <input type="hidden" name="product_id" class="amazon_product_id" value="">
                            <div class="form-group">
                                <input type="text" name="first_name" placeholder="Guest First Name">
                            </div>
                            <div class="form-group">
                                <input type="text" name="last_name" placeholder="Guest Last Name">
                            </div>
                            <div class="form-group w-100">
                                <textarea name="message" placeholder=" Gift Message (Optional)"></textarea>
                            </div>
                            <div class="button-wrap">
                                <button type="submit" class="custom_button">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    return ob_get_clean();
}
add_shortcode('mark_as_purchased_popup', 'mark_as_purchased_shortcode');
// ✅ Extract ASIN from the provided URL
// ✅ Expand Amazon short URLs before checking duplicates
// ✅ Expand short Amazon URLs before processing
function expand_amazon_url($url)
{
    $parsed = parse_url($url);
    if (empty($parsed['host']))
        return $url;
    $host = strtolower($parsed['host']);
    $short_domains = ['amzn.to', 'amzn.in', 'amzn.eu'];
    if (!in_array($host, $short_domains)) {
        return $url;
    }
    // Try HEAD first
    $response = wp_remote_head($url, ['redirection' => 5]);
    if (is_wp_error($response) || empty(wp_remote_retrieve_header($response, 'location'))) {
        // fallback to GET if HEAD fails
        $response = wp_remote_get(
            $url,
            [
                'timeout'     => 30,
                'redirection' => 5,
                'headers' => [
                    'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36',
                    'Accept' =>
                    'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' =>
                    'en-IN,en;q=0.9',
                    'Cache-Control' =>
                    'no-cache',
                    'Pragma' =>
                    'no-cache',
                ],
            ]
        );
    }
    if (is_wp_error($response))
        return $url;
    $final_url = wp_remote_retrieve_header($response, 'location');
    if (!$final_url && isset($response['http_response'])) {
        $final_url = $response['http_response']->get_response_object()->url;
    }
    return $final_url ?: $url;
}
// ✅ Main AJAX handler
add_action(
    'wp_ajax_fetch_amazon_product',
    'fetch_amazon_product_callback'
);
function fetch_amazon_product_callback()
{
    if (empty($_POST['amazon_url'])) {
        wp_send_json_error(
            'No URL provided'
        );
    }
    $user_id =
        get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(
            'You must be logged in to create a product'
        );
    }
    $raw_url =
        trim(
            wp_unslash(
                $_POST['amazon_url']
            )
        );
    if (
        !preg_match(
            '#^https?://#i',
            $raw_url
        )
    ) {
        $raw_url =
            'https://' .
            ltrim(
                $raw_url,
                '/'
            );
    }
    $url =
        esc_url_raw(
            $raw_url
        );
    if (!$url) {
        wp_send_json_error(
            'Please provide a valid product URL.'
        );
    }
    /*
     * Amazon
     */
    if (
        mlf_is_amazon_product_url(
            $url
        )
    ) {
        mlf_import_amazon_product(
            $url,
            $user_id,
            false
        );
        return;
    }
    /*
     * Other websites
     */
    mlf_import_non_amazon_product(
        $url,
        $user_id,
        false
    );
}
/**
 * =========================================================
 * MULTI STORE PRODUCT IMPORT
 * Amazon existing functionality remains unchanged.
 * Non-Amazon products use generic JSON-LD / OpenGraph parsing.
 * =========================================================
 */
function mlf_is_amazon_product_url($url)
{
    $url = trim((string) $url);
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }
    $host = strtolower(
        (string) wp_parse_url(
            $url,
            PHP_URL_HOST
        )
    );
    $host = preg_replace('/^www\./', '', $host);
    if (!$host) {
        return false;
    }
    if (
        in_array(
            $host,
            [
                'amzn.to',
                'amzn.in',
                'amzn.eu',
            ],
            true
        )
    ) {
        return true;
    }
    return (
        $host === 'amazon.in' ||
        substr($host, -10) === '.amazon.in' ||
        $host === 'amazon.com' ||
        substr($host, -11) === '.amazon.com'
    );
}
function mlf_external_platform_name($url)
{
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    $host = preg_replace('/^www\./', '', $host);
    $map = [
        'flipkart.com'       => 'Flipkart',
        'meesho.com'         => 'Meesho',
        'tataneu.com'        => 'Tata Neu',
        'snapdeal.com'       => 'Snapdeal',
        'myntra.com'         => 'Myntra',
        'ajio.com'           => 'AJIO',
        'nykaa.com'          => 'Nykaa',
        'tatacliq.com'       => 'Tata CLiQ',
        'thesouledstore.com' => 'The Souled Store',
        'jiomart.com'        => 'JioMart',
        'bigbasket.com'      => 'BigBasket',
        'blinkit.com'        => 'Blinkit',
        'zepto.com'          => 'Zepto',
        'zeptonow.com'       => 'Zepto',
        'swiggy.com'         => 'Swiggy Instamart',
        'firstcry.com'       => 'FirstCry',
        'lenskart.com'       => 'Lenskart',
        'pepperfry.com'      => 'Pepperfry',
        '1mg.com'            => 'Tata 1mg',
        'pharmeasy.in'       => 'PharmEasy',
    ];
    foreach ($map as $domain => $label) {
        if (
            $host === $domain ||
            substr($host, - (strlen($domain) + 1)) === '.' . $domain
        ) {
            return $label;
        }
    }
    // Unknown website also allowed.
    if ($host) {
        $parts = explode('.', $host);
        $name = count($parts) > 1
            ? $parts[count($parts) - 2]
            : $parts[0];
        return ucwords(
            str_replace(['-', '_'], ' ', $name)
        );
    }
    return 'Other Store';
}
function mlf_normalize_external_product_url($url)
{
    $url = esc_url_raw($url);
    if (!$url) {
        return '';
    }
    // Remove only common tracking parameters.
    $url = remove_query_arg([
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'fbclid',
        'gclid',
        'ref',
        'ref_',
        'source',
    ], $url);
    $parts = wp_parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return '';
    }
    $scheme = !empty($parts['scheme'])
        ? strtolower($parts['scheme'])
        : 'https';
    $host = strtolower($parts['host']);
    $path = isset($parts['path'])
        ? rtrim($parts['path'], '/')
        : '';
    $query = !empty($parts['query'])
        ? '?' . $parts['query']
        : '';
    return $scheme . '://' . $host . $path . $query;
}
/**
 * Recursively find Schema.org Product JSON-LD.
 */
function mlf_find_product_jsonld($data)
{
    if (!is_array($data)) {
        return null;
    }
    if (isset($data['@type'])) {
        $types = is_array($data['@type'])
            ? $data['@type']
            : [$data['@type']];
        foreach ($types as $type) {
            if (strcasecmp((string) $type, 'Product') === 0) {
                return $data;
            }
        }
    }
    foreach ($data as $value) {
        if (is_array($value)) {
            $found = mlf_find_product_jsonld($value);
            if ($found) {
                return $found;
            }
        }
    }
    return null;
}
/**
 * Get meta content using XPath.
 */
function mlf_xpath_meta_content(
    DOMXPath $xpath,
    $attribute,
    $value
) {
    $value = strtolower($value);
    $query = sprintf(
        '//meta[
            translate(
                @%1$s,
                "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                "abcdefghijklmnopqrstuvwxyz"
            )="%2$s"
        ]/@content',
        $attribute,
        $value
    );
    $nodes = $xpath->query($query);
    if ($nodes && $nodes->length) {
        return trim(
            $nodes->item(0)->nodeValue
        );
    }
    return '';
}
function mlf_external_image_from_jsonld($image)
{
    if (is_string($image)) {
        return $image;
    }
    if (is_array($image)) {
        if (
            isset($image['url']) &&
            is_string($image['url'])
        ) {
            return $image['url'];
        }
        if (
            isset($image['contentUrl']) &&
            is_string($image['contentUrl'])
        ) {
            return $image['contentUrl'];
        }
        foreach ($image as $item) {
            $found =
                mlf_external_image_from_jsonld($item);
            if ($found) {
                return $found;
            }
        }
    }
    return '';
}
/**
 * Add a URL-only registry item when a store cannot be fetched.
 */
function mlf_add_external_fallback_product(
    $url,
    $user_id,
    $platform = '',
    $product_data = []
) {
    $url = esc_url_raw($url);
    $user_id = absint($user_id);
    if (!$url || !$user_id || !wp_http_validate_url($url)) {
        wp_send_json_error('Please provide a valid public product URL.');
        return;
    }
    $normalized_url = mlf_normalize_external_product_url($url);
    $existing = get_posts([
        'post_type'      => 'amazon_product',
        'post_status'    => ['publish', 'draft'],
        'author'         => $user_id,
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => 'product_link_normalized',
                'value'   => $normalized_url,
                'compare' => '=',
            ],
        ],
    ]);
    if (!empty($existing)) {
        wp_send_json_error(
            '🚫 This product already exists in your registry.'
        );
        return;
    }
    $platform = sanitize_text_field((string) $platform);
    if ($platform === '') {
        $platform = mlf_external_platform_name($url);
    }
    if (!$platform) {
        $platform = 'External Store';
    }
    $title = $platform === 'External Store'
        ? 'External Product'
        : $platform . ' Product';
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $segments = array_values(
        array_filter(explode('/', trim($path, '/')), 'strlen')
    );
    // Prefer a readable slug; skip IDs and generic path segments.
    foreach ($segments as $segment) {
        $candidate = rawurldecode($segment);
        $candidate = preg_replace('/[-_]+/', ' ', $candidate);
        $candidate = trim(wp_strip_all_tags($candidate));
        if (
            strlen($candidate) >= 4 &&
            preg_match('/[a-z]/i', $candidate) &&
            !in_array(
                strtolower($candidate),
                ['product', 'products', 'item', 'detail', 'details'],
                true
            ) &&
            str_word_count($candidate) >= 2
        ) {
            $title = ucwords($candidate);
            break;
        }
    }
    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($title),
        'post_type'    => 'amazon_product',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
        'post_content' => '',
    ], true);
    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error('Unable to create this registry product.');
        return;
    }
    update_post_meta(
        $post_id,
        'product_link_normalized',
        $normalized_url
    );
    update_post_meta($post_id, 'external_platform', $platform);
    update_post_meta($post_id, '_ml_source_url', $url);
    update_post_meta($post_id, '_mlf_import_fallback', 1);
    $price = $product_data['price'] ?? '';
    $image = $product_data['image'] ?? '';
    if (!empty($product_data['title'])) {
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $product_data['title'],
            'post_content' => $product_data['description'] ?? '',
        ]);
    }
    update_post_meta($post_id, 'product_link', $url);
    update_post_meta($post_id, 'price', $price);
    if (function_exists('update_field')) {
        update_field('product_link', $url, $post_id);
        update_field('price', $price, $post_id);
    }
    if ($image && wp_http_validate_url($image)) {
        update_post_meta(
            $post_id,
            '_ml_external_image',
            esc_url_raw($image)
        );
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = media_sideload_image(
            $image,
            $post_id,
            get_the_title($post_id),
            'id'
        );
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
    wp_send_json_success([
        'message'  => 'Product added to your Registry. Please add the missing price and image manually.',
        'post_id'  => $post_id,
        'fallback' => true,
    ]);
}
/**
 * Import a NON-AMAZON external product.
 *
 * IMPORTANT:
 * We still save into amazon_product CPT so your existing
 * Wishlist functionality does not need to change.
 */
function mlf_import_non_amazon_product(
    $url,
    $user_id,
    $admin_request = false
) {
    $url = esc_url_raw($url);
    if (
        !$url ||
        !wp_http_validate_url($url)
    ) {
        wp_send_json_error(
            'Please provide a valid public product URL.'
        );
    }
    $normalized_url =
        mlf_normalize_external_product_url($url);
    $platform =
        mlf_external_platform_name($url);
    /**
     * Check duplicate for current user.
     */
    $existing = get_posts([
        'post_type'   => 'amazon_product',
        'post_status' => [
            'publish',
            'draft',
        ],
        'author' => $user_id,
        'meta_query' => [
            [
                'key'     => 'product_link_normalized',
                'value'   => $normalized_url,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);
    if (!empty($existing)) {
        wp_send_json_error(
            '🚫 This product already exists in your registry.'
        );
    }
    /**
     * Safe WordPress request.
     */
    $response = wp_safe_remote_get(
        $url,
        [
            'timeout'     => 25,
            'redirection' => 5,
            'headers' => [
                'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36',
                'Accept-Language' =>
                'en-IN,en;q=0.9',
                'Accept' =>
                'text/html,application/xhtml+xml',
            ],
        ]
    );
    if (is_wp_error($response)) {
        $shopify_data = mlf_get_shopify_product_data($url);
        if (!empty($shopify_data)) {
            mlf_add_external_fallback_product(
                $url,
                $user_id,
                $platform,
                $shopify_data
            );
        } else {
            mlf_add_external_fallback_product(
                $url,
                $user_id,
                $platform
            );
        }
        return;
    }
    $status = (int) wp_remote_retrieve_response_code($response);
    $html = (string) wp_remote_retrieve_body($response);
    if (
        $status < 200 ||
        $status >= 400 ||
        strlen(trim($html)) < 200
    ) {
        $shopify_data = mlf_get_shopify_product_data($url);
        if (!empty($shopify_data)) {
            mlf_add_external_fallback_product(
                $url,
                $user_id,
                $platform,
                $shopify_data
            );
        } else {
            mlf_add_external_fallback_product(
                $url,
                $user_id,
                $platform
            );
        }
        return;
    }
    /**
     * Parse HTML.
     */
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    @$doc->loadHTML(
        '<?xml encoding="utf-8" ?>' .
$html
);
$xpath = new DOMXPath($doc);
libxml_clear_errors();
/**
* First try JSON-LD Product schema.
*/
$product = null;
$scripts =
$xpath->query(
'//script[@type="application/ld+json"]'
);
if ($scripts) {
foreach ($scripts as $script) {
$json =
html_entity_decode(
trim(
$script->textContent
),
ENT_QUOTES | ENT_HTML5,
'UTF-8'
);
$data =
json_decode(
$json,
true
);
if (is_array($data)) {
$product =
mlf_find_product_jsonld(
$data
);
if ($product) {
break;
}
}
}
}
$title = '';
$description = '';
$image = '';
$price = '';
$currency = '';
$brand = '';
$category = '';
$shopify_data = mlf_get_shopify_product_data($url);
/**
* JSON-LD values.
*/
if (is_array($product)) {
if (!empty($product['name'])) {
$title =
wp_strip_all_tags(
$product['name']
);
}
if (!empty($product['description'])) {
$description =
wp_kses_post(
$product['description']
);
}
if (!empty($product['image'])) {
$image =
mlf_external_image_from_jsonld(
$product['image']
);
}
if (
!empty($product['category']) &&
is_string($product['category'])
) {
$category =
sanitize_text_field(
$product['category']
);
}
/**
* Brand.
*/
if (!empty($product['brand'])) {
if (
is_string(
$product['brand']
)
) {
$brand =
sanitize_text_field(
$product['brand']
);
} elseif (
is_array(
$product['brand']
) &&
!empty($product['brand']['name'])
) {
$brand =
sanitize_text_field(
$product['brand']['name']
);
}
}
/**
* Price.
*/
$offers =
$product['offers'] ?? [];
if (isset($offers[0])) {
$offers = $offers[0];
}
if (is_array($offers)) {
$price =
$offers['price']
?? (
$offers['lowPrice']
?? (
$offers['highPrice']
?? ''
)
);
$currency =
$offers['priceCurrency']
?? '';
}
}
/**
* OpenGraph / HTML fallbacks.
*/
if (!$title) {
$title =
mlf_xpath_meta_content(
$xpath,
'property',
'og:title'
);
}
if (!$title) {
$title =
mlf_xpath_meta_content(
$xpath,
'name',
'twitter:title'
);
}
if (!$title) {
$title_nodes =
$xpath->query('//title');
if (
$title_nodes &&
$title_nodes->length
) {
$title =
trim(
$title_nodes
->item(0)
->textContent
);
}
}
if (!$description) {
$description =
mlf_xpath_meta_content(
$xpath,
'property',
'og:description'
);
}
if (!$description) {
$description =
mlf_xpath_meta_content(
$xpath,
'name',
'description'
);
}
if (!$image) {
$image =
mlf_xpath_meta_content(
$xpath,
'property',
'og:image'
);
}
if (!$image) {
$image =
mlf_xpath_meta_content(
$xpath,
'name',
'twitter:image'
);
}
if (!$price) {
$price =
mlf_xpath_meta_content(
$xpath,
'property',
'product:price:amount'
);
}
if (!$price) {
$price_nodes =
$xpath->query(
'//*[@itemprop="price"]/@content |
//*[@itemprop="price"]'
);
if (
$price_nodes &&
$price_nodes->length
) {
$node =
$price_nodes->item(0);
$price =
trim(
$node->nodeValue
?: $node->textContent
);
}
}
if (!$currency) {
$currency =
mlf_xpath_meta_content(
$xpath,
'property',
'product:price:currency'
);
}
/**
* Clean title.
*/
/**
* Shopify fallback for missing metadata.
*/
if (!empty($shopify_data)) {
if (!$title && !empty($shopify_data['title'])) {
$title = $shopify_data['title'];
}
if (!$description && !empty($shopify_data['description'])) {
$description = $shopify_data['description'];
}
if (!$image && !empty($shopify_data['image'])) {
$image = $shopify_data['image'];
}
if (
($price === '' || !is_numeric(
str_replace(',', '', (string) $price)
)) &&
$shopify_data['price'] !== ''
) {
$price = $shopify_data['price'];
}
}
$title =
trim(
wp_strip_all_tags(
html_entity_decode(
$title,
ENT_QUOTES | ENT_HTML5,
'UTF-8'
)
)
);
if (!$title) {
wp_send_json_error(
'Product title could not be detected. Please use the manual option.'
);
}
/**
* Normalize numeric price.
*/
if ($price !== '') {
$price =
preg_replace(
'/[^0-9.,]/',
'',
(string) $price
);
$price =
str_replace(
',',
'',
$price
);
}
/**
* Create product.
*
* Keeping amazon_product post type intentionally
* so your existing registry continues to work.
*/
$post_id =
wp_insert_post(
[
'post_title' => $title,
'post_content' => $description,
'post_type' =>
'amazon_product',
'post_status' =>
'publish',
'post_author' =>
$user_id,
],
true
);
if (is_wp_error($post_id)) {
wp_send_json_error(
'Something went wrong while creating the product.'
);
}
/**
* Extra data.
*/
update_post_meta(
$post_id,
'product_link_normalized',
$normalized_url
);
update_post_meta(
$post_id,
'external_platform',
$platform
);
update_post_meta(
$post_id,
'external_currency',
sanitize_text_field(
$currency
)
);
/**
* Keep using your existing ACF field names.
*/
if (function_exists('update_field')) {
update_field(
'product_link',
$url,
$post_id
);
if ($price !== '') {
update_field(
'price',
$price,
$post_id
);
}
if ($brand !== '') {
update_field(
'amazon_brand',
$brand,
$post_id
);
}
if ($category !== '') {
update_field(
'amazon_category',
$category,
$post_id
);
}
} else {
update_post_meta(
$post_id,
'product_link',
$url
);
if ($price !== '') {
update_post_meta(
$post_id,
'price',
$price
);
}
if ($brand !== '') {
update_post_meta(
$post_id,
'amazon_brand',
$brand
);
}
if ($category !== '') {
update_post_meta(
$post_id,
'amazon_category',
$category
);
}
}
// For stores such as Meesho/Tira, try product-specific lazy-loaded images
// when JSON-LD and Open Graph omit the product's main image.
$image = mlf_import_image_url($image, $url);
if (!$image) {
$hero_queries = [
'//*[@itemprop="image"]/@content',
'//meta[@name="twitter:image:src"]/@content',
'//link[@rel="image_src"]/@href',
'//img[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"product-image")]',
'//img[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"productimage")]',
'//img[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"product_main")]',
'//img[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"product-main")]',
'//img[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"productdetail")]',
];
foreach ($hero_queries as $hero_query) {
$nodes = $xpath->query($hero_query);
if (!$nodes) {
continue;
}
foreach ($nodes as $node) {
$candidates = [];
if ($node instanceof DOMAttr) {
$candidates[] = $node->value;
} elseif ($node instanceof DOMElement) {
foreach (['data-zoom-image', 'data-old-hires', 'data-src', 'data-original', 'src'] as $attr) {
$candidates[] = $node->getAttribute($attr);
}
$srcset = $node->getAttribute('srcset');
if ($srcset) {
$first = trim(explode(',', $srcset)[0]);
$candidates[] = preg_split('/\s+/', $first)[0] ?? '';
}
}
foreach ($candidates as $candidate) {
$resolved = mlf_import_image_url($candidate, $url);
if ($resolved) {
$image = $resolved;
break 3;
}
}
}
}
}
if (!$image && $platform === 'Meesho') {
$image = mlf_extract_meesho_product_image($html, $url);
}
update_post_meta($post_id, '_ml_source_url', $url);
if ($image) {
update_post_meta($post_id, '_ml_external_image', $image);
}
/**
* Featured image.
*/
if (
$image &&
wp_http_validate_url($image)
) {
require_once
ABSPATH .
'wp-admin/includes/image.php';
require_once
ABSPATH .
'wp-admin/includes/file.php';
require_once
ABSPATH .
'wp-admin/includes/media.php';
$attachment_id =
media_sideload_image(
$image,
$post_id,
$title,
'id'
);
if (
!is_wp_error(
$attachment_id
) &&
$attachment_id
) {
set_post_thumbnail(
$post_id,
$attachment_id
);
}
}
if (isset($attachment_id) && is_wp_error($attachment_id)) {
update_post_meta($post_id, '_ml_image_error', $attachment_id->get_error_message());
error_log('External product image download failed for post ' . $post_id . ': ' . $attachment_id->get_error_message());
} elseif (!$image) {
update_post_meta($post_id, '_ml_image_error', 'The store HTML contained no usable product image.');
}
/**
* Response.
*/
$data = [
'message' =>
$platform .
' product added successfully.',
'title' =>
$title,
'price' =>
$price,
'category' =>
$category,
'brand' =>
$brand,
'image' =>
$image,
'platform' =>
$platform,
// Keep key name for existing JS compatibility.
'amazon_url' =>
$url,
'post_id' =>
$post_id,
];
if ($admin_request) {
wp_send_json_success(
$data
);
}
wp_send_json_success(
'Your gift was successfully added to your Registry. You can edit or add more gifts at any time!'
);
}
/**
* Extract Meesho's product image from HTML or embedded hydration JSON.
* Meesho often embeds its gallery URLs in script data instead of img tags.
*/
function mlf_extract_meesho_product_image($html, $base_url = '')
{
if (!is_string($html) || $html === '') {
return '';
}
$decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
// Handle JSON-escaped slashes and URL escapes found in hydration payloads.
$decoded = str_replace(
['\\/', '\\u002F', '\\u002f', '\\u003A', '\\u003a', '\\u0026'],
['/', '/', '/', ':', ':', '&'],
$decoded
);
$decoded = preg_replace('/\\\\u002[eE]/', '.', $decoded);
// Restrict candidates to Meesho's product image CDN, not generic page assets.
if (preg_match_all(
'~(?:https?:)?//images\\.meesho\\.com/images/products/[a-zA-Z0-9_./%-]+\\.(?:webp|jpe?g|png)(?:\\?[a-zA-Z0-9_=&%-]*)?~i',
$decoded,
$matches
)) {
foreach (array_unique($matches[0]) as $candidate) {
$image = mlf_import_image_url($candidate, $base_url);
if ($image) {
return $image;
}
}
}
return '';
}
/** Recover a missing Meesho image once per day at most per product. */
function mlf_recover_meesho_product_image($post_id)
{
$post_id = absint($post_id);
if (!$post_id || get_post_type($post_id) !== 'amazon_product') {
return '';
}
$url = (string) get_post_meta($post_id, '_ml_source_url', true);
if (!$url) {
$url = (string) get_post_meta($post_id, 'product_link', true);
}
$host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
if (!in_array($host, ['meesho.com', 'www.meesho.com'], true) || !wp_http_validate_url($url)) {
return '';
}
$key = 'ml_meesho_img_' . $post_id;
if (get_transient($key)) {
return '';
}
set_transient($key, 1, DAY_IN_SECONDS);
$response = wp_safe_remote_get($url, [
'timeout' => 12,
'redirection' => 3,
'headers' => [
'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36',
'Accept' => 'text/html,application/xhtml+xml',
'Accept-Language' => 'en-IN,en;q=0.9',
],
]);
if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
return '';
}
$image = mlf_extract_meesho_product_image((string) wp_remote_retrieve_body($response), $url);
if ($image) {
update_post_meta($post_id, '_ml_external_image', $image);
delete_post_meta($post_id, '_ml_image_error');
}
return $image;
}
/** Resolve an extracted image URL, including relative CDN paths. */
function mlf_import_image_url($candidate, $base_url = '')
{
$candidate = trim(html_entity_decode((string) $candidate, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
if ($candidate === '' || preg_match('#^(data|javascript|blob):#i', $candidate)) {
return '';
}
if (strpos($candidate, '//') === 0) {
$candidate = 'https:' . $candidate;
} elseif (!preg_match('#^https?://#i', $candidate)) {
if (!$base_url) {
return '';
}
$candidate = WP_Http::make_absolute_url($candidate, $base_url);
}
return wp_http_validate_url($candidate) ? esc_url_raw($candidate) : '';
}
/** Read only product hero nodes, Product schema and primary-image data. */
function mlf_extract_amazon_image(DOMXPath $xpath, $html, $base_url)
{
$queries = [
'//img[@id="landingImage"]',
'//img[@id="imgBlkFront"]',
'//img[@id="ebooksImgBlkFront"]',
'//div[@id="imgTagWrapperId"]//img',
'//div[@id="main-image-container"]//img',
];
foreach ($queries as $query) {
foreach ($xpath->query($query) as $node) {
$candidates = [$node->getAttribute('data-old-hires')];
$dynamic = json_decode($node->getAttribute('data-a-dynamic-image'), true);
if (is_array($dynamic)) {
uasort($dynamic, function ($a, $b) {
$a = is_array($a) ? $a : [];
$b = is_array($b) ? $b : [];
return ((float) ($b[0] ?? 0) * (float) ($b[1] ?? 0))
<=> ((float) ($a[0] ?? 0) * (float) ($a[1] ?? 0));
    });
    $candidates = array_merge($candidates, array_keys($dynamic));
    }
    $candidates[] = $node->getAttribute('data-src');
    $candidates[] = $node->getAttribute('src');
    foreach ($candidates as $candidate) {
    $image = mlf_import_image_url($candidate, $base_url);
    if ($image) {
    return $image;
    }
    }
    }
    }
    foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
    $data = json_decode(trim($script->textContent), true);
    $product = mlf_find_product_jsonld($data);
    if ($product && !empty($product['image'])) {
    $image = mlf_import_image_url(mlf_external_image_from_jsonld($product['image']), $base_url);
    if ($image) {
    return $image;
    }
    }
    }
    // Amazon's primary variant image: avoid unrelated recommendations.
    foreach ($xpath->query('//script[not(@src)]') as $script) {
    if (preg_match(
    '/["\x27]colorImages["\x27]\s*:\s*\{\s*["\x27]initial["\x27]\s*:\s*(\[.*?\])/s',
    $script->textContent,
    $match
    )) {
    $images = json_decode($match[1], true);
    if (is_array($images) && !empty($images[0]) && is_array($images[0])) {
    foreach (['hiRes', 'large', 'mainUrl'] as $key) {
    $image = mlf_import_image_url($images[0][$key] ?? '', $base_url);
    if ($image) {
    return $image;
    }
    }
    }
    }
    }
    foreach (['//meta[@property="og:image"]/@content', '//meta[@name="twitter:image"]/@content'] as $query) {
    foreach ($xpath->query($query) as $node) {
    $image = mlf_import_image_url($node->nodeValue, $base_url);
    if ($image) {
    return $image;
    }
    }
    }
    return '';
    }
    /** Share the same saved-image fallback across external-product templates. */
    function mlf_registry_image_url($post_id, $size = 'full', $fallback = '')
    {
    $image = get_the_post_thumbnail_url($post_id, $size);
    if ($image) {
    return $image;
    }
    $image = mlf_import_image_url(get_post_meta($post_id, '_ml_external_image', true));
    if (!$image) {
    $image = mlf_recover_meesho_product_image($post_id);
    }
    return $image ?: $fallback;
    }
    /** Shared fallback for wishlist, single external product and received gifts. */
    if (!function_exists('mlf_get_registry_product_image')) {
    function mlf_get_registry_product_image($post_id, $size = 'full')
    {
    return mlf_registry_image_url(
    absint($post_id),
    $size,
    get_template_directory_uri() . '/assets/images/default_gift.png'
    );
    }
    }
    function mlf_import_amazon_product(
    $url,
    $user_id,
    $admin_request = false
    ) {
    $url_expanded =
    expand_amazon_url($url);
    /*
    * -----------------------
    * Extract ASIN
    * -----------------------
    */
    $asin = '';
    if (
    preg_match(
    '/(?:dp|gp\/product)\/([A-Z0-9]{10})/i',
    $url_expanded,
    $m
    )
    ) {
    $asin =
    strtoupper($m[1]);
    }
    if (!$asin) {
    wp_send_json_error(
    'Amazon ASIN could not be detected from this URL.'
    );
    }
    /*
    * -----------------------
    * Duplicate check
    * -----------------------
    */
    $existing = get_posts([
    'post_type' =>
    'amazon_product',
    'post_status' => [
    'publish',
    'draft'
    ],
    'author' =>
    $user_id,
    'meta_query' => [
    [
    'key' =>
    'asin_code',
    'value' =>
    $asin,
    'compare' =>
    '='
    ]
    ],
    'posts_per_page' =>
    1,
    'fields' =>
    'ids'
    ]);
    if (!empty($existing)) {
    wp_send_json_error(
    '🚫 This product already exists in your registry.'
    );
    }
    /*
    * -----------------------
    * Amazon request
    * -----------------------
    */
    // Old working Amazon PHP-cURL fetch flow, with SSL validation retained.
    if (!function_exists('curl_init')) {
    wp_send_json_error('PHP cURL is not enabled on the server.');
    }
    // Remove Amazon tracking parameters while keeping product identity.
    $amazon_host = strtolower((string) wp_parse_url($url_expanded, PHP_URL_HOST));
    $amazon_host = preg_replace('/^www\./', '', $amazon_host);
    $amazon_domain = ($amazon_host === 'amazon.com' || substr($amazon_host, -11) === '.amazon.com') ? 'amazon.com' :
    'amazon.in';
    $request_url = 'https://www.' . $amazon_domain . '/dp/' . $asin;
    $ch = curl_init();
    curl_setopt_array($ch, [
    CURLOPT_URL => $request_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    CURLOPT_TIMEOUT => 25,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
    'Accept-Language: en-US,en;q=0.9',
    'Accept-Encoding: identity'
    ],
    ]);
    $html = curl_exec($ch);
    $curl_error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($html === false || $curl_error) {
    error_log('Amazon cURL failed: ' . $curl_error);
    wp_send_json_error('Unable to fetch Amazon product details. Please try again or add manually.');
    }
    error_log('Amazon fetch HTTP ' . $status . ' | ASIN ' . $asin . ' | bytes ' . strlen($html));
    if ($status !== 200) {
    wp_send_json_error('Amazon request returned HTTP ' . $status . '. Please try again later or add manually.');
    }
    if (strlen($html) < 5000) { error_log('AMAZON DEBUG: ASIN=' . $asin);
        error_log(' AMAZON DEBUG: HTTP status=' . $status);
        error_log(' AMAZON DEBUG: HTML bytes=' . strlen($html));
        error_log(' AMAZON DEBUG: HTML preview=' .
            substr(preg_replace(' /\s+/', ' ' , $html), 0, 1000)); wp_send_json_error('Amazon returned a short response
        (' . strlen($html) . ' bytes). Check PHP error logs for details.' ); } if (
        stripos($html, 'Enter the characters you see below' ) !==false ||
        stripos($html, 'Sorry, we just need to make sure' ) !==false ) { wp_send_json_error('Amazon requires additional
        verification. Please add the product manually.'); } libxml_use_internal_errors(true); $doc=new DOMDocument();
        @$doc->loadHTML($html);
        $xpath =
        new DOMXPath($doc);
        /*
        * -----------------------
        * TITLE
        * -----------------------
        */
        $product_title = '';
        $title_queries = [
        '//span[@id="productTitle"]',
        '//meta[@property="og:title"]',
        '//title'
        ];
        foreach (
        $title_queries
        as $query
        ) {
        $node =
        $xpath
        ->query($query)
        ->item(0);
        if (!$node) {
        continue;
        }
        if (
        $node->nodeName ===
        'meta'
        ) {
        $candidate =
        $node->getAttribute(
        'content'
        );
        } else {
        $candidate =
        $node->textContent;
        }
        $candidate =
        trim(
        html_entity_decode(
        $candidate,
        ENT_QUOTES |
        ENT_HTML5,
        'UTF-8'
        )
        );
        if ($candidate) {
        $product_title =
        $candidate;
        break;
        }
        }
        if ($product_title && !$xpath->query('//span[@id="productTitle"]')->length) {
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
        $schema = mlf_find_product_jsonld(json_decode(trim($script->textContent), true));
        if ($schema && !empty($schema['name']) && is_string($schema['name'])) {
        $product_title = trim($schema['name']);
        break;
        }
        }
        $product_title = preg_replace('/^Amazon\.(?:com|in)\s*:\s*/i', '', $product_title);
        $product_title = trim(explode(' | ', $product_title, 2)[0]);
        }
        /*
        * -----------------------
        * PRICE
        * -----------------------
        */
        $product_price = '';
        $price_queries = [
        '//div[@id="corePriceDisplay_desktop_feature_div"]//span[contains(@class,"a-offscreen")]',
        '//div[@id="corePrice_feature_div"]//span[contains(@class,"a-offscreen")]',
        '//div[@id="apex_desktop"]//span[contains(@class,"a-offscreen")]',
        '//span[contains(@class,"a-price")]//span[contains(@class,"a-offscreen")]',
        '//span[@id="priceblock_ourprice"]',
        '//span[@id="priceblock_dealprice"]',
        '//span[contains(@class,"a-price-whole")]'
        ];
        foreach (
        $price_queries
        as $query
        ) {
        $node =
        $xpath
        ->query($query)
        ->item(0);
        if (!$node) {
        continue;
        }
        $candidate =
        trim(
        $node->textContent
        );
        if ($candidate) {
        $product_price =
        preg_replace(
        '/[^0-9.]/',
        '',
        str_replace(
        ',',
        '',
        $candidate
        )
        );
        if ($product_price) {
        break;
        }
        }
        }
        /*
        * -----------------------
        * IMAGE
        * -----------------------
        */
        $product_image = mlf_extract_amazon_image($xpath, $html, $request_url);
        error_log(
        'AMAZON TITLE: ' .
        $product_title
        );
        error_log(
        'AMAZON PRICE: ' .
        $product_price
        );
        error_log(
        'AMAZON IMAGE: ' .
        $product_image
        );
        /*
        * -----------------------
        * Title required
        * -----------------------
        */
        if (!$product_title) {
        file_put_contents(
        WP_CONTENT_DIR .
        '/amazon-debug.html',
        $html
        );
        wp_send_json_error(
        'Amazon returned HTML but product title was not found. Debug saved in wp-content/amazon-debug.html'
        );
        }
        if (!$product_image) {
        error_log('Amazon import: no product image extracted for ASIN ' . $asin);
        wp_send_json_error('Amazon returned no usable product image. Please add the product and image manually.');
        return;
        }
        /*
        * -----------------------
        * Create post
        * -----------------------
        */
        $post_id =
        wp_insert_post(
        [
        'post_title' =>
        sanitize_text_field(
        $product_title
        ),
        'post_content' =>
        '',
        'post_type' =>
        'amazon_product',
        'post_status' =>
        'publish',
        'post_author' =>
        $user_id
        ],
        true
        );
        if (
        is_wp_error($post_id) ||
        !$post_id
        ) {
        wp_send_json_error(
        'Unable to create Amazon product.'
        );
        }
        /*
        * -----------------------
        * Save metadata
        * -----------------------
        */
        update_post_meta(
        $post_id,
        'asin_code',
        $asin
        );
        update_post_meta(
        $post_id,
        'external_platform',
        'Amazon'
        );
        update_post_meta(
        $post_id,
        '_ml_source_url',
        $url_expanded
        );
        /*
        * Affiliate link
        */
        $affiliate_link =
        'https://www.' . $amazon_domain . '/dp/' . rawurlencode($asin);
        // Keep the existing India affiliate tag only for the India storefront.
        if ($amazon_domain === 'amazon.in') {
        $affiliate_link .= '?tag=est20240a-21';
        }
        if (
        function_exists(
        'update_field'
        )
        ) {
        update_field(
        'product_link',
        $affiliate_link,
        $post_id
        );
        if ($product_price !== '') {
        update_post_meta(
        $post_id,
        'price',
        $product_price
        );
        if (function_exists('update_field')) {
        update_field(
        'price',
        $product_price,
        $post_id
        );
        }
        }
        } else {
        update_post_meta(
        $post_id,
        'product_link',
        $affiliate_link
        );
        if ($product_price) {
        update_post_meta(
        $post_id,
        'price',
        $product_price
        );
        }
        }
        /*
        * -----------------------
        * Download image
        * -----------------------
        */
        if ($product_image) {
        update_post_meta($post_id, '_ml_external_image', esc_url_raw($product_image));
        require_once
        ABSPATH .
        'wp-admin/includes/image.php';
        require_once
        ABSPATH .
        'wp-admin/includes/file.php';
        require_once
        ABSPATH .
        'wp-admin/includes/media.php';
        $attachment_id =
        media_sideload_image(
        $product_image,
        $post_id,
        $product_title,
        'id'
        );
        if (
        !is_wp_error(
        $attachment_id
        )
        ) {
        set_post_thumbnail(
        $post_id,
        $attachment_id
        );
        } else {
        error_log(
        'AMAZON IMAGE ERROR (external URL saved): ' .
        $attachment_id
        ->get_error_message()
        );
        }
        }
        /*
        * -----------------------
        * Response
        * -----------------------
        */
        $result = [
        'message' =>
        'Amazon product added successfully.',
        'post_id' =>
        $post_id,
        'title' =>
        $product_title,
        'price' =>
        $product_price,
        'image' =>
        $product_image,
        'asin' =>
        $asin,
        'affiliate_link' =>
        $affiliate_link
        ];
        if ($admin_request) {
        wp_send_json_success(
        $result
        );
        }
        wp_send_json_success([
        'message' => ($price !== '' && $image)
        ? 'Your gift was successfully added to your Registry.'
        : 'Product added to your Registry. Please add the missing price or image manually.',
        'post_id' => $post_id,
        'fallback' => true,
        'price' => $price,
        'image' => $image,
        ]);
        }
        // Manual Store product data in post type
        add_action('wp_ajax_manual_amazon_product', 'manual_amazon_product_callback');
        function manual_amazon_product_callback()
        {
        $user_id = get_current_user_id();
        if (!$user_id) {
        wp_send_json_error('You must be logged in to add a product.');
        }
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $price = isset($_POST['amazon_price']) ? sanitize_text_field($_POST['amazon_price']) : '';
        $url = isset($_POST['amazon_url'])
        ? esc_url_raw($_POST['amazon_url'])
        : '';
        if (empty($url)) {
        wp_send_json_error(
        'Please provide a valid product URL.'
        );
        }
        $is_amazon =
        mlf_is_amazon_product_url(
        $url
        );
        /**
        * Only expand Amazon short URLs.
        * Other sites remain untouched.
        */
        if (
        $is_amazon &&
        function_exists(
        'expand_amazon_url'
        )
        ) {
        $expanded_url =
        expand_amazon_url(
        $url
        );
        if (empty($expanded_url)) {
        $expanded_url = $url;
        }
        } else {
        $expanded_url = $url;
        }
        $normalized_url =
        mlf_normalize_external_product_url(
        $expanded_url
        );
        /**
        * Only Amazon URLs can have ASIN.
        */
        $asin = '';
        if ($is_amazon) {
        if (
        preg_match(
        '/(?:dp|gp\/product)\/([A-Z0-9]{10})/i',
        $expanded_url,
        $m
        )
        ) {
        $asin =
        strtoupper(
        $m[1]
        );
        } elseif (
        preg_match(
        '/([A-Z0-9]{10})/i',
        $expanded_url,
        $m2
        )
        ) {
        $asin =
        strtoupper(
        $m2[1]
        );
        }
        }
        // ---------- DUPLICATE CHECK ----------
        $meta_query = ['relation' => 'OR'];
        if (!empty($asin)) {
        // check explicit saved asin_code
        $meta_query[] = [
        'key' => 'asin_code',
        'value' => $asin,
        'compare' => '='
        ];
        // check whether product_link contains ASIN (covers admin long URLs where asin_code wasn't saved)
        $meta_query[] = [
        'key' => 'product_link',
        'value' => $asin,
        'compare' => 'LIKE'
        ];
        }
        // check normalized URL exact matches (if some posts save normalized form)
        $meta_query[] = [
        'key' => 'product_link',
        'value' => $normalized_url,
        'compare' => '='
        ];
        $meta_query[] = [
        'key' => 'product_link_normalized',
        'value' => $normalized_url,
        'compare' => '='
        ];
        $existing = get_posts([
        'post_type' => 'amazon_product',
        'post_status' => ['publish', 'draft'],
        'meta_query' => $meta_query,
        'posts_per_page' => 1,
        'fields' => 'ids'
        ]);
        if (!empty($existing)) {
        wp_send_json_error('🚫 This product is already exists.');
        }
        // ---------- CREATE PRODUCT ----------
        $post_id = wp_insert_post([
        'post_title' => $name,
        'post_type' => 'amazon_product',
        'post_status' => 'publish',
        'post_author' => $user_id,
        ]);
        if (!$post_id) {
        wp_send_json_error('Failed to create product.');
        }
        // Save ASIN if found
        if (!empty($asin)) {
        update_post_meta($post_id, 'asin_code', $asin);
        }
        // Save normalized product link meta so future checks are easier
        update_post_meta($post_id, 'product_link_normalized', $normalized_url);
        // Save affiliate link in product_link field (ACF or meta; you're already using update_field)
        // $tracking_id = 'modernlifaf03-21';
        // $affiliate_link = !empty($asin) ? "https://www.amazon.in/dp/{$asin}?tag={$tracking_id}" : $expanded_url;
        /**
        * Amazon:
        * Keep your existing affiliate behaviour.
        *
        * Other stores:
        * Save original retailer URL.
        */
        if ($is_amazon) {
        //$tracking_id = 'modernlifaf03-21';
        $tracking_id = 'modernlifafa02';
        $affiliate_link =
        !empty($asin)
        ? "https://www.amazon.in/dp/{$asin}?tag={$tracking_id}"
        : $expanded_url;
        } else {
        $affiliate_link =
        $expanded_url;
        }
        update_post_meta(
        $post_id,
        'external_platform',
        $is_amazon
        ? 'Amazon India'
        : mlf_external_platform_name(
        $expanded_url
        )
        );
        if (function_exists('update_field')) {
        update_field('product_link', $affiliate_link, $post_id);
        } else {
        update_post_meta($post_id, 'product_link', $affiliate_link);
        }
        // Save price
        if (function_exists('update_field')) {
        update_field('price', $price, $post_id);
        } else {
        update_post_meta($post_id, 'price', $price);
        }
        // ---------- IMAGE HANDLING ----------
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = false;
        if (!empty($_FILES['amazon_image']['name'])) {
        $attachment_id = media_handle_upload('amazon_image', $post_id);
        }
        if (is_wp_error($attachment_id) || !$attachment_id) {
        // Default image fallback
        $default_img = get_template_directory_uri() . '/assets/images/default_gift.png';
        $tmp = download_url($default_img);
        if (!is_wp_error($tmp)) {
        $file_array = [
        'name' => basename($default_img),
        'tmp_name' => $tmp,
        ];
        $attachment_id = media_handle_sideload($file_array, $post_id, 'Default gift image');
        if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        $attachment_id = false;
        }
        }
        }
        if (!is_wp_error($attachment_id) && $attachment_id) {
        set_post_thumbnail($post_id, $attachment_id);
        }
        wp_send_json_success([
        'message' => 'Your gift was successfully added to your Registry.',
        'post_id' => $post_id,
        ]);
        }
        // Mark as purchased form save and send email to original user
        add_action('wp_ajax_save_purchased_form', 'save_purchased_form_callback');
        add_action('wp_ajax_nopriv_save_purchased_form', 'save_purchased_form_callback');
        function save_purchased_form_callback()
        {
        error_log('AJAX: save_purchased_form_callback STARTED');
        global $wpdb;
        $table_name = $wpdb->prefix . 'purchased_submissions';
        if (empty($_POST['form_data'])) {
        error_log('AJAX: form_data missing');
        wp_send_json_error('Invalid form data');
        wp_die();
        }
        parse_str($_POST['form_data'], $form);
        $original_user_id = intval($_POST['original_user_id'] ?? 0);
        error_log('AJAX: original_user_id = ' . $original_user_id);
        $result = $wpdb->insert($table_name, [
        'user_id' => $original_user_id,
        'product_id' => intval($form['product_id'] ?? 0),
        'first_name' => sanitize_text_field($form['first_name'] ?? ''),
        'last_name' => sanitize_text_field($form['last_name'] ?? ''),
        'message' => sanitize_textarea_field($form['message'] ?? ''),
        'created_at' => current_time('mysql'),
        ]);
        if ($result === false) {
        error_log('DB INSERT FAILED: ' . $wpdb->last_error);
        wp_send_json_error('DB insert failed');
        wp_die();
        }
        $submission_id = $wpdb->insert_id;
        error_log("DB INSERT SUCCESS, submission_id = {$submission_id}");
        $scheduled = wp_schedule_single_event(
        time() + 5,
        'send_purchased_gift_email_bg',
        [$submission_id, $original_user_id, $form]
        );
        if ($scheduled === false) {
        error_log('CRON NOT SCHEDULED');
        } else {
        error_log('CRON SCHEDULED SUCCESSFULLY');
        }
        wp_remote_post(
        site_url('/wp-cron.php'),
        [
        'timeout' => 0.01,
        'blocking' => false,
        ]
        );
        error_log('AJAX: wp-cron.php triggered');
        wp_send_json_success([
        'message' => 'Thank you for sharing the details.'
        ]);
        wp_die();
        }
        // ADD BACKGROUND EMAIL HOOK
        add_action('send_purchased_gift_email_bg', 'send_purchased_gift_email_bg_callback', 10, 3);
        function send_purchased_gift_email_bg_callback($submission_id, $original_user_id, $form)
        {
        error_log('🔥 CRON: send_purchased_gift_email_bg_callback STARTED');
        error_log("🔥 CRON DATA: submission_id={$submission_id}, user_id={$original_user_id}");
        $user_info = get_userdata($original_user_id);
        if (!$user_info) {
        error_log("🔴 CRON: User not found: {$original_user_id}");
        return;
        }
        $user_email = $user_info->user_email;
        error_log("🟢 CRON: Sending email to {$user_email}");
        $couple_name = get_user_meta($original_user_id, 'first_name', true);
        if (empty($couple_name)) {
        $couple_name = $user_info->display_name;
        }
        $subject = "A gift from your registry has been purchased ✨";
        $header_img = get_option('woocommerce_email_header_image', '');
        // 🎁 Gather dynamic email data
        $guest_name = trim(($form['first_name'] ?? '') . ' ' . ($form['last_name'] ?? ''));
        $gift_name = get_the_title(intval($form['product_id'] ?? 0));
        $retailer = (!empty($form['amazon_url'])) ? 'Other Retailer' : 'Amazon';
        $gift_msg = !empty($form['message']) ? wp_strip_all_tags($form['message']) : '—';
        $home_url = home_url();
        $year = date('Y');
        $email_footer = "
        <tr style='background-color: #DD1265;'>
            <td style='padding: 14px 20px; background: #ffc4d8;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%'
                    style='max-width: 650px; width: 100%;'>
                    <tr>
                        <td
                            style='display: block; width: 100%; text-align: center; padding-bottom: 10px; font-family: Segoe UI, Helvetica, sans-serif; font-size: 14px; color: #5c164c; line-height: 20px;'>
                            <a href='{$home_url}/privacy-policy/'
                                style='color: #5c164c; text-decoration: none; margin-right: 8px;'>Privacy Policy</a> |
                            <a href='{$home_url}/contact-us/'
                                style='color: #5c164c; text-decoration: none; margin: 0 8px;'>Contact Us</a> |
                            <a href='{$home_url}' style='color: #5c164c; text-decoration: none; margin: 0 8px;'>Find a
                                Couple</a> |
                            <a href='{$home_url}/sign-up/'
                                style='color: #5c164c; text-decoration: none; margin-left: 8px;'>Create a Registry</a>
                        </td>
                    </tr>
                    <tr>
                        <td style='text-align: center;'>
                            <a href='https://www.instagram.com/accounts/login/?next=%2Fmodernlifafa%2F&source=omni_redirect'
                                target='_blank'><img
                                    src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/instagram.png'
                                    alt='Instagram' width='28' height='28' style='margin: 5px;'></a>
                            <a href='https://www.facebook.com/people/Modern-Lifafa/61573859936910/?mibextid=wwXIfr&rdid=COOnmMhuMwzyWZcG&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1XfR4u8pcd%2F%3Fmibextid%3DwwXIfr'
                                target='_blank'><img
                                    src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/facebook.png'
                                    alt='Facebook' width='28' height='28' style='margin: 5px;'></a>
                        </td>
                    </tr>
                    <tr>
                        <td style='text-align: center; padding-top: 5px;'>
                            <p style='margin: 0; font-size: 14px; color: #5c164c;'>Modern Lifafa ©{$year}, All Rights
                                Reserved</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        ";
        // 💌 Email Body
        // Marke as purchased email template
        $message = "
        <html>

        <body style='font-family:Segoe UI,Helvetica,sans-serif;'>
            <table style='max-width:650px;width:100%;border:1px solid #DD1265;margin:0 auto;border-collapse:collapse;'>
                <tr>
                    <td align='center' style='padding:0; margin:0;'>
                        <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png'
                                alt='Modern Lifafa Header' width='650'
                                style='display:block;width:100%;max-width:650px;height:auto;margin:0 auto;' />
                        </picture>
                    </td>
                </tr>
                <tr>
                    <td style='padding:25px;font-size:16px;color:#333;line-height:1.6;'>
                        <p>Hi <strong style='color:#DD1265;'>{$couple_name}</strong>,</p>
                        <p>Exciting news — <strong>{$guest_name}</strong> just marked a gift from your registry as
                            purchased
                            💝</p>
                        <p style='margin-top:0px !important; margin-bottom:0px !important;'>Here are the details:</p>
                        <table style='width:100%;border-collapse:collapse;'>
                            <tr>
                                <td style='padding:2px 0;'><strong>Gift:</strong> {$gift_name}</td>
                            </tr>
                            <tr>
                                <td style='padding:2px 0;'><strong>Retailer:</strong> {$retailer}</td>
                            </tr>
                            <tr>
                                <td style='padding:2px 0;'><strong>Message:</strong> \"{$gift_msg}\"</td>
                            </tr>
                        </table>
                        <p style='margin-top:10px;'>
                            Your registry has been updated automatically, so this gift will now show as
                            “Purchased” to other guests.
                        </p>
                        <p style='margin-top:20px;'>
                            You can log in anytime to view your full list of gifts and see any messages guests have left
                            for
                            you:.
                        </p>
                        <p style='margin-top:15px;'>
                            <a href='{$home_url}/wishlist/'
                                style='background-color:#DD1265;color:#fff;text-decoration:none;padding:10px 18px;border-radius:6px;display:inline-block;font-weight:600;'>View
                                My Registry</a>
                        </p>
                        <p style='margin-top:30px;'>We’re so thrilled to be part of your celebration!</p>
                        <p style='margin-top:10px;'><strong>– The Modern Lifafa Team</strong></p>
                    </td>
                </tr>
                {$email_footer}
            </table>
        </body>

        </html>";
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if (wp_mail($user_email, $subject, $message, $headers)) {
        error_log("✅ CRON: Email sent successfully");
        } else {
        error_log("❌ CRON: wp_mail FAILED");
        }
        }
        // Admin side Create amazon post type code
        // 1. Register Custom Post Type: Amazon Products
        if (!defined('ABSPATH'))
        exit;
        add_action('init', function () {
        register_post_type('amazon_product', [
        'labels' => [
        'name' => 'Amazon Products',
        'singular_name' => 'Amazon Product',
        ],
        'public' => true,
        'menu_icon' => 'dashicons-cart',
        'show_in_menu' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        ]);
        register_taxonomy('amazon_category', 'amazon_product', [
        'label' => 'Categories',
        'hierarchical' => true,
        'public' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'amazon-category'],
        ]);
        // ✅ Register Amazon Brand taxonomy for Amazon Products
        register_taxonomy('amazon_brand', 'amazon_product', [
        'label' => 'Brands',
        'hierarchical' => true,
        'public' => true,
        'show_admin_column' => true, // shows the Brand column in admin table
        'rewrite' => ['slug' => 'amazon-brand'],
        ]);
        });
        /**
        * Remove default "Add New" submenu
        */
        add_action('admin_menu', function () {
        remove_submenu_page('edit.php?post_type=amazon_product', 'post-new.php?post_type=amazon_product');
        });
        /**
        * Add custom submenu "Add Amazon Product"
        */
        add_action('admin_menu', function () {
        add_submenu_page(
        'edit.php?post_type=amazon_product',
        'Add Amazon Product',
        'Add Amazon Product',
        'manage_options',
        'add-amazon-product',
        'render_amazon_product_form'
        );
        });
        // Admin side adding Amazon Products url in backend Admin side
        function render_amazon_product_form()
        {
        ?>
        <div class="wrap">
            <h1>Add Amazon Product</h1>
            <form method="post">
                <?php wp_nonce_field('amazon_product_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="amazon_url">Amazon Product URL</label></th>
                        <td><input type="text" name="amazon_url" id="amazon_product_url"
                                class="regular-text amazon_product_url_admin"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" id="fetch_amazon_url" name="amazon_add_product"
                        class="button button-primary fetch_amazon_url_admin">Add
                        Product</button>
                    <a href="<?php echo admin_url('post-new.php?post_type=amazon_product'); ?>"
                        class="button manual-url-btn">Add Product
                        Manually</a>
                </p>
            </form>
        </div>
        <?php
}
/**
 * Handle form submission
 */
// add_action('admin_init', function () {
//     if (isset($_POST['amazon_add_product']) && check_admin_referer('amazon_product_nonce')) {
//         $url = esc_url_raw($_POST['amazon_url']);
//         // Basic placeholder post (extend later with scraping/API)
//         wp_insert_post([
//             'post_title' => 'Amazon Product - ' . $url,
//             'post_type' => 'amazon_product',
//             'post_status' => 'publish',
//             'meta_input' => ['amazon_url' => $url],
//         ]);
//         wp_redirect(admin_url('edit.php?post_type=amazon_product'));
//         exit;
//     }
// });
add_action(
    'wp_ajax_admin_fetch_amazon_product',
    'mlf_admin_fetch_product'
);
function mlf_admin_fetch_product()
{
    if (
        empty($_POST['add_amazon_product_nonce']) ||
        !wp_verify_nonce(
            $_POST['add_amazon_product_nonce'],
            'add_amazon_product_action'
        )
    ) {
        wp_send_json_error('Security check failed');
    }
    if (empty($_POST['amazon_url'])) {
        wp_send_json_error('No URL provided');
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('You must be logged in');
    }
    $raw_url = trim(
        wp_unslash($_POST['amazon_url'])
    );
    if (
        !preg_match(
            '#^https?://#i',
            $raw_url
        )
    ) {
        $raw_url =
            'https://' .
            ltrim($raw_url, '/');
    }
    $url = esc_url_raw($raw_url);
    if (!$url) {
        wp_send_json_error(
            'Invalid product URL.'
        );
    }
    /*
     * Non Amazon
     */
    if (!mlf_is_amazon_product_url($url)) {
        mlf_import_non_amazon_product(
            $url,
            $user_id,
            true
        );
        return;
    }
    /*
     * Amazon
     */
    mlf_import_amazon_product(
        $url,
        $user_id,
        true
    );
}
// Admin side to fetch amazon product using amazon url
// add_action('wp_ajax_admin_fetch_amazon_product', 'admin_fetch_amazon_product');
// function admin_fetch_amazon_product()
// {
//     error_log("admin_fetch_amazon_product called");
//     if (!isset($_POST['add_amazon_product_nonce']) || !wp_verify_nonce($_POST['add_amazon_product_nonce'], 'add_amazon_product_action')) {
//         wp_send_json_error('Security check failed');
//     }
//     if (empty($_POST['amazon_url'])) {
//         wp_send_json_error('No URL provided');
//     }
//     $user_id = get_current_user_id();
//     if (!$user_id) {
//         wp_send_json_error('You must be logged in');
//     }
//     // ✅ Expand short URLs first
//     $url = esc_url_raw($_POST['amazon_url']);
//     $url_expanded = expand_amazon_url($url);
//     error_log("Expanded Amazon URL: " . $url_expanded);
//     // ✅ Extract ASIN cleanly
//     $asin = '';
//     if (preg_match('/(?:dp|gp\/product)\/([A-Z0-9]{10})/i', $url_expanded, $m)) {
//         $asin = strtoupper($m[1]);
//     }
//     // ✅ Prevent duplicate product by ASIN
//     if ($asin) {
//         $existing = get_posts([
//             'post_type' => 'amazon_product',
//             'post_status' => 'publish',
//             'meta_query' => [
//                 [
//                     'key' => 'asin_code',
//                     'value' => $asin,
//                     'compare' => '=',
//                 ],
//             ],
//             'posts_per_page' => 1,
//             'fields' => 'ids',
//         ]);
//         if (!empty($existing)) {
//             wp_send_json_error('🚫 This product already exists in your registry.');
//         }
//     }
//     // --- CURL fetch ---
//     $ch = curl_init();
//     curl_setopt_array($ch, [
//         CURLOPT_URL => $url_expanded,
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_SSL_VERIFYPEER => false,
//         CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
//         CURLOPT_HTTPHEADER => [
//             "Accept-Language: en-US,en;q=0.9",
//             "Accept-Encoding: identity"
//         ]
//     ]);
//     $html = curl_exec($ch);
//     $curl_error = curl_error($ch);
//     curl_close($ch);
//     if (!$html) {
//         wp_send_json_error('Unable to fetch Amazon page. Error: ' . $curl_error);
//     }
//     libxml_use_internal_errors(true);
//     $doc = new DOMDocument();
//     $doc->loadHTML($html);
//     $xpath = new DOMXPath($doc);
//     // echo '<pre>';
//     // var_dump($html);
//     // echo '</pre>';
//     // --- Extract title ---
//     $titleNode = $xpath->query('//span[@id="productTitle"]')->item(0);
//     if (!$titleNode) {
//         wp_send_json_error('Product title not found. Amazon may block requests.');
//     }
//     $product_title = trim($titleNode->textContent);
//     // --- Extract price ---
//     $priceNode = $xpath->query('//span[@id="priceblock_ourprice" or @id="priceblock_dealprice"]')->item(0);
//     if (!$priceNode)
//         $priceNode = $xpath->query('//span[contains(@class,"a-price-whole")]')->item(0);
//     $decimalNode = $xpath->query('//span[contains(@class,"a-price-fraction")]')->item(0);
//     $product_price = '';
//     if ($priceNode) {
//         $priceWhole = trim($priceNode->textContent);
//         $decimalPart = $decimalNode ? trim($decimalNode->textContent) : '';
//         if (!preg_match('/[.,]\d{1,2}$/u', $priceWhole)) {
//             $priceWhole .= ($decimalPart !== '' ? '.' . $decimalPart : '');
//         }
//         $combined = preg_replace('/[^\d\.]/u', '', $priceWhole);
//         $combined = preg_replace('/\.{2,}/', '.', $combined);
//         $combined = trim($combined, '.');
//         $product_price = (string) intval(floor(floatval($combined)));
//     }
//     // --- Extract category ---
//     $product_category = '';
//     $categoryNode = $xpath->query('//ul[@id="wayfinding-breadcrumbs_feature_div"]//li//a')->item(0);
//     if ($categoryNode) {
//         $product_category = trim($categoryNode->textContent);
//     } else {
//         $altCat = $xpath->query('//a[contains(@class,"a-link-normal a-color-tertiary")]')->item(0);
//         if ($altCat)
//             $product_category = trim($altCat->textContent);
//     }
//     // --- BRAND extraction ---
//     $product_brand = '';
//     // First, try structured JSON data inside <script type="application/ld+json">
//     if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/is', $html, $jsonMatch)) {
//         $jsonContent = trim($jsonMatch[1]);
//         $decoded = json_decode($jsonContent, true);
//         if (is_array($decoded)) {
//             // Case 1: Single product schema
//             if (!empty($decoded['brand']['name'])) {
//                 $product_brand = trim($decoded['brand']['name']);
//             }
//             // Case 2: Sometimes array of JSON-LD objects
//             elseif (isset($decoded[0]['brand']['name'])) {
//                 $product_brand = trim($decoded[0]['brand']['name']);
//             }
//         }
//     }
//     // Fallback 1: "Visit the XYZ Store" link
//     if (empty($product_brand)) {
//         $brandNode = $xpath->query('//a[@id="bylineInfo"]')->item(0);
//         if ($brandNode) {
//             $brandText = trim($brandNode->textContent);
//             $product_brand = preg_replace('/^Visit the\s+|\s+Store$/i', '', $brandText);
//         }
//     }
//     // Fallback 2: Brand row in product details
//     if (empty($product_brand)) {
//         $brandNode = $xpath->query('//tr[th[contains(text(),"Brand")]]/td')->item(0);
//         if ($brandNode) {
//             $product_brand = trim($brandNode->textContent);
//         }
//     }
//     if (empty($product_brand)) {
//         $product_brand = 'Unknown';
//     }
//     // --- Description ---
//     $descNode = $xpath->query('//div[@id="productDescription"]')->item(0);
//     if (!$descNode)
//         $descNode = $xpath->query('//div[@id="feature-bullets"]')->item(0);
//     $product_desc = $descNode ? $doc->saveHTML($descNode) : '';
//     $product_desc = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $product_desc);
//     $product_desc = preg_replace('/See more product details|Show More/is', '', $product_desc);
//     // --- Image ---
//     $imageNode = $xpath->query('//img[@id="landingImage"]')->item(0);
//     $product_image = '';
//     if ($imageNode) {
//         $product_image = $imageNode->getAttribute('src');
//     } elseif (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
//         $product_image = $matches[1];
//     }
//     // --- Create post ---
//     $post_data = [
//         'post_title' => $product_title,
//         'post_content' => $product_desc,
//         'post_type' => 'amazon_product',
//         'post_status' => 'publish',
//         'post_author' => $user_id,
//     ];
//     $post_id = wp_insert_post($post_data);
//     if (!$post_id) {
//         wp_send_json_error('Failed to create post.');
//     }
//     // --- Save ACF/meta fields ---
//     if ($product_price)
//         update_field('price', $product_price, $post_id);
//     if ($asin)
//         update_post_meta($post_id, 'asin_code', $asin);
//     // --- Affiliate link ---
//     $tracking_id = 'est20240a-21';
//     $affiliate_link = $asin ? 'https://www.amazon.in/dp/' . $asin . '?tag=' . $tracking_id : $url_expanded;
//     update_field('product_link', $affiliate_link, $post_id);
//     // --- Assign Category ---
//     // if (!empty($product_category)) {
//     //  $term = term_exists($product_category, 'amazon_category');
//     //  if (!$term)
//     //      $term = wp_insert_term($product_category, 'amazon_category');
//     //  if (!is_wp_error($term))
//     //      wp_set_object_terms($post_id, intval($term['term_id']), 'amazon_category');
//     // }
//     if (!empty($product_category)) {
//         if (function_exists('update_field')) {
//             update_field('amazon_category', $product_category, $post_id);
//         } else {
//             update_post_meta($post_id, 'amazon_category', $product_category);
//         }
//     }
//     // --- Assign Brand ---
//     // if (!empty($product_brand)) {
//     //  $term = term_exists($product_brand, 'amazon_brand');
//     //  if (!$term)
//     //      $term = wp_insert_term($product_brand, 'amazon_brand');
//     //  if (!is_wp_error($term))
//     //      wp_set_object_terms($post_id, intval($term['term_id']), 'amazon_brand');
//     // }
//     if (!empty($product_brand)) {
//         if (function_exists('update_field')) {
//             update_field('amazon_brand', $product_brand, $post_id);
//         } else {
//             update_post_meta($post_id, 'amazon_brand', $product_brand);
//         }
//     }
//     // --- Featured image ---
//     if ($product_image) {
//         require_once ABSPATH . 'wp-admin/includes/image.php';
//         require_once ABSPATH . 'wp-admin/includes/file.php';
//         require_once ABSPATH . 'wp-admin/includes/media.php';
//         $attach_id = media_sideload_image($product_image, $post_id, $product_title, 'id');
//         if (!is_wp_error($attach_id)) {
//             set_post_thumbnail($post_id, $attach_id);
//         }
//     }
//     wp_send_json_success([
//         'message' => 'Amazon product added successfully.',
//         'title' => $product_title,
//         'price' => $product_price,
//         'category' => $product_category,
//         'brand' => $product_brand,
//         'image' => $product_image,
//         'asin' => $asin,
//         'amazon_url' => $url_expanded, // 👈 added this line
//         'post_id' => $post_id,
//     ]);
// }
/**
 * Prevent duplicate Amazon URLs in ACF field 'product_link' for amazon_product CPT
 */
// Your original validate_value filter (no wp_die)
add_filter('acf/validate_value/name=product_link', function ($valid, $value, $field, $input) {
    if ($valid !== true)
        return $valid;
    if (empty($value))
        return $valid;
    $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0;
    $asin = '';
    if (preg_match('/\/(dp|gp\/product)\/([A-Z0-9]{10})/i', $value, $matches)) {
        $asin = strtoupper($matches[2]);
    }
    $args = [
        'post_type' => 'amazon_product',
        'post_status' => ['publish', 'draft', 'pending'],
        'post__not_in' => [$post_id],
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'product_link',
                'value' => $value,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'product_link',
                'value' => $asin,
                'compare' => 'LIKE',
            ],
        ],
        'posts_per_page' => 1,
        'fields' => 'ids',
    ];
    $existing = get_posts($args);
    if (!empty($existing)) {
        return '⚠️ This Amazon URL is already used in another Amazon Product. Please use a unique URL.';
    }
    return $valid;
}, 10, 4);
// ==========================================
// Redirect to external product purchase popup
// ==========================================
function amazon_buy_popup_message_shortcode()
{
    ob_start();
    $post_id = get_queried_object_id();
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    // Existing product URL field
    $product_link = get_field('product_link', $post_id);
    /**
     * First try saved platform name.
     */
    $platform_name = get_post_meta(
        $post_id,
        'external_platform',
        true
    );
    /**
     * If platform not saved, detect from URL.
     */
    if (empty($platform_name) && $product_link) {
        if (function_exists('mlf_external_platform_name')) {
            $platform_name = mlf_external_platform_name(
                $product_link
            );
        } else {
            $host = strtolower(
                (string) wp_parse_url(
                    $product_link,
                    PHP_URL_HOST
                )
            );
            $host = preg_replace(
                '/^www\./',
                '',
                $host
            );
            $platforms = [
                'amazon.in'          => 'Amazon',
                'flipkart.com'       => 'Flipkart',
                'meesho.com'         => 'Meesho',
                'tataneu.com'        => 'Tata Neu',
                'snapdeal.com'       => 'Snapdeaal',
                'myntra.com'         => 'Myntra',
                'ajio.com'           => 'AJIO',
                'nykaa.com'          => 'Nykaa',
                'nykaafashion.com'   => 'Nykaa Fashion',
                'tatacliq.com'       => 'Tata CLiQ',
                'thesouledstore.com' => 'The Souled Store',
                'jiomart.com'        => 'JioMart',
                'bigbasket.com'      => 'BigBasket',
                'blinkit.com'        => 'Blinkit',
                'zepto.com'          => 'Zepto',
                'zeptonow.com'       => 'Zepto',
                'swiggy.com'         => 'Swiggy Instamart',
                'firstcry.com'       => 'FirstCry',
                'lenskart.com'       => 'Lenskart',
                'pepperfry.com'      => 'Pepperfry',
                '1mg.com'            => 'Tata 1mg',
                'pharmeasy.in'       => 'PharmEasy',
            ];
            $platform_name = 'External Store';
            foreach ($platforms as $domain => $name) {
                if (
                    $host === $domain ||
                    substr(
                        $host,
                        - (strlen($domain) + 1)
                    ) === '.' . $domain
                ) {
                    $platform_name = $name;
                    break;
                }
            }
        }
    }
    if (empty($platform_name)) {
        $platform_name = 'External Store';
    }
    /**
     * Normalize old Amazon names.
     */
    if (
        strtolower($platform_name) === 'amazon india' ||
        strtolower($platform_name) === 'amazon.in'
    ) {
        $platform_name = 'Amazon';
    }
?>
        <div class="popup-cls-adding-all-added new-gift-buyers-amazon-popup-cls-adding">
            <div class="popup-cls-adding-all-added new-gift-buyers-amazon-popup-cls-adding-inner">
                <img class="broken-link-cls-adding-here" src="<?php echo esc_url(
                                                                get_template_directory_uri() .
                                                                    '/assets/images/view-amazon-img.png'
                                                            ); ?>" alt="<?php echo esc_attr($platform_name); ?>">
                <div class="custom-popup-message" style="text-align:center;">
                    <p class="new-para-cls-adding">
                         Just a little detour
                        <?php //  echo esc_html($platform_name); ?>
                    </p>
                    <p class="purchased-all-typed-cls">This gift is sold on another website. Once you've picked it up,
                    come back and mark it as gifted, so the couple won't receive it twice. </p>
                    <div class="continue-notification-popup-cls-added">
                        <div class="continue-amazon-button-cls-here">
                            <button class="ok-buy-now notification-btn"
                                data-product-url="<?php echo esc_url($product_link); ?>">SHOP THIS GIFT
                            </button>
                        </div>
                        <div class="continue-goback-button-cls-here">
                            <button class="amazon-redirect-popup-close">
                                GO BACK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    return ob_get_clean();
}
add_shortcode(
    'amazon_buy_popup_message',
    'amazon_buy_popup_message_shortcode'
);
// Add custom columns: Image, Price, Category for amazon_product post type listing in backend
// Add Featured Image and Price columns for amazon_product
add_filter('manage_amazon_product_posts_columns', function ($columns) {
    $new = [];
    foreach ($columns as $key => $value) {
        $new[$key] = $value;
        if ($key === 'cb') {
            $new['featured_image'] = '<span class="wc-image tips">Image</span>';
        }
        if ($key === 'title') {
            $new['price'] = 'Price';
        }
    }
    return $new;
});
add_action('manage_amazon_product_posts_custom_column', function ($column, $post_id) {
    if ($column === 'featured_image') {
        $thumbnail = get_the_post_thumbnail($post_id, [60, 60]);
        echo $thumbnail ?: '—';
    }
    if ($column === 'price') {
        $raw_price = get_field('price', $post_id); // ACF field
        $price = preg_replace('/\D/', '', $raw_price);
        $currency = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
        echo $price ? $currency . esc_html($price) : '—';
    }
}, 10, 2);
add_action('admin_head', function () {
    echo '<style>
        .column-featured_image { width: 80px; text-align: center; }
        .column-featured_image img { border-radius: 4px; }
        th.column-featured_image .wc-image.tips { font-weight: 600; display: inline-block; }
        .column-price { width: 100px; text-align: left; } /* Align price left */
    </style>';
});
// Add Amazon Category filter dropdown in Amazon Products admin list
// ✅ Add Category & Brand filters in Amazon Products admin list
add_action('restrict_manage_posts', function () {
    global $typenow;
    if ($typenow !== 'amazon_product') {
        return;
    }
    // ----- Amazon Category Filter -----
    $taxonomy_category = 'amazon_category';
    $selected_category = isset($_GET[$taxonomy_category]) ? $_GET[$taxonomy_category] : '';
    wp_dropdown_categories([
        'show_option_all' => __('All Amazon Categories'),
        'taxonomy' => $taxonomy_category,
        'name' => $taxonomy_category,
        'orderby' => 'name',
        'selected' => $selected_category,
        'hierarchical' => true,
        'depth' => 0,
        'show_count' => true,
        'hide_empty' => true,
    ]);
    // ----- Amazon Brand Filter -----
    $taxonomy_brand = 'amazon_brand'; // Replace with your brand taxonomy slug
    $selected_brand = isset($_GET[$taxonomy_brand]) ? $_GET[$taxonomy_brand] : '';
    wp_dropdown_categories([
        'show_option_all' => __('All Amazon Brands'),
        'taxonomy' => $taxonomy_brand,
        'name' => $taxonomy_brand,
        'orderby' => 'name',
        'selected' => $selected_brand,
        'hierarchical' => true,
        'depth' => 0,
        'show_count' => true,
        'hide_empty' => false,
    ]);
});
// ✅ Make the filters work properly in query
add_filter('parse_query', function ($query) {
    global $pagenow;
    $taxonomy = 'amazon_category'; // Replace with your taxonomy slug
    $q_vars = &$query->query_vars;
    if ($pagenow === 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] === 'amazon_product' && !empty($q_vars[$taxonomy])) {
        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
        if ($term) {
            $q_vars[$taxonomy] = $term->slug;
        }
    }
    $taxonomy_brand = 'amazon_brand'; // Brand taxonomy
    if ($pagenow === 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] === 'amazon_product' && !empty($q_vars[$taxonomy_brand])) {
        $term = get_term_by('id', $q_vars[$taxonomy_brand], $taxonomy_brand);
        if ($term) {
            $q_vars[$taxonomy_brand] = $term->slug;
        }
    }
});
/**
 * Wishlist Popup Modal
 */
function wishlist_popup_markup()
{
?>
        <!-- Wishlist Popup -->
        <div class="wishlist-popup new-wishlist-popup-cls-adding" id="wishlist-popup" style="display:none;">
            <div class="wishlist-popup-inner">
                <div class="wishlist-all-typed-cls-adding">
                    <button class="wishlist-popup-close" aria-label="Close">
                        <svg width="48px" height="48px" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z"
                                fill="#cbb04b" />
                        </svg>
                    </button>
                    <div class="wishlist-popup-content">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/default_gift1.png'); ?>"
                            alt="Gift Icon" class="wishlist-icon" />
                        <h3>Added to Your Registry!</h3>
                        <button class="wishlist-view-registry"
                            onclick="window.location.href='<?php echo home_url('/wishlist/') ?>';">VIEW
                            REGISTRY</button>
                        <a href="<?php echo home_url('/products/') ?>" class="wishlist-keep-shopping">Keep Shopping for
                            More
                            Gifts</a>
                    </div>
                </div>
            </div>
        </div>
        <style>
        /* Popup styles */
        .wishlist-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .wishlist-popup-inner {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            position: relative;
            width: 90%;
            max-width: 380px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: popupFadeIn 0.3s ease;
        }

        .wishlist-popup-close {
            position: absolute;
            top: 10px;
            right: 12px;
            background: transparent;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #c00;
        }

        .wishlist-popup-content img.wishlist-icon {
            width: 60px;
            margin-bottom: 15px;
        }

        .wishlist-popup-content h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #5b2b6d;
        }

        .wishlist-view-registry {
            background: #c7b65e;
            color: #000 !important;
            font-weight: 700;
            padding: 14px 30px 14px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 18px;
            font-family: "DM Sans", Sans-serif;
            font-size: 18px;
            line-height: 22px;
        }

        .wishlist-view-registry:hover {
            background: #b99a3a;
        }

        .wishlist-keep-shopping {
            display: block;
            color: #dd1265 !important;
            text-decoration: none;
            font-weight: 400;
            margin-top: 0px !important;
            font-size: 18px !important;
            line-height: 22px !important;
        }

        .wishlist-keep-shopping:hover {
            text-decoration: underline;
        }

        .toast:has(.toast-message:contains("This product is already in your wishlist.")) {
            display: none !important;
        }

        @keyframes popupFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>
        <?php
}
add_action('wp_footer', 'wishlist_popup_markup');
// Price format save only numbers in ACF field 'price'
add_filter('acf/update_value/name=price', function ($value, $post_id, $field) {
    $value = str_replace(',', '', $value);
    $value = preg_replace('/[^\d.]/', '', $value);
    return $value;
}, 10, 3);
// Print Amazon response for testing purpose 
// $url = 'https://www.amazon.in/OnePlus-Massive-MediaTek-Dimensity-Infinity/dp/B0FCMLCX46?ref=dlx_deals_dg_dcl_B0FCMLCX46_dt_sl10_57_pi&pf_rd_r=0S8X02EARNAE1WQT1FAH&pf_rd_p=4aa1ec30-42c5-481c-8f72-6d38d1fd7d57';
// $url_expanded = expand_amazon_url($url);
// $ch = curl_init();
// curl_setopt_array($ch, [
//  CURLOPT_URL => $url_expanded,
//  CURLOPT_RETURNTRANSFER => true,
//  CURLOPT_FOLLOWLOCATION => true,
//  CURLOPT_SSL_VERIFYPEER => false,
//  CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
//  CURLOPT_HTTPHEADER => [
//      "Accept-Language: en-US,en;q=0.9",
//      "Accept-Encoding: identity"
//  ]
// ]);
// $html = curl_exec($ch);
// $curl_error = curl_error($ch);
// curl_close($ch);
// if (!$html) {
//  wp_send_json_error('Unable to fetch Amazon page. Error: ' . $curl_error);
// }
// libxml_use_internal_errors(true);
// $doc = new DOMDocument();
// $doc->loadHTML($html);
// $xpath = new DOMXPath($doc);
// echo "<pre>";
// echo htmlentities($html);
// echo "</pre>";
// exit;
// on click cart update 
add_action('save_post_amazon_product', 'mlf_save_admin_asin_code', 20, 3);
function mlf_save_admin_asin_code($post_id, $post, $update)
{
    // Avoid infinite loops
    remove_action('save_post_amazon_product', 'mlf_save_admin_asin_code');
    // Get product link
    $product_link = get_post_meta($post_id, 'product_link', true);
    if (!empty($product_link)) {
        // Extract ASIN
        if (preg_match('/(?:dp|gp\/product)\/([A-Z0-9]{10})/i', $product_link, $m)) {
            $asin = strtoupper($m[1]);
            update_post_meta($post_id, 'asin_code', $asin);
            error_log("Admin product updated → Saved ASIN: $asin");
        }
    }
    // Re-enable hook
    add_action('save_post_amazon_product', 'mlf_save_admin_asin_code', 20, 3);
}
// 18 - january-2026
/**
 * Add custom fields to Dokan Vendor Dashboard Product Form
 */
add_filter('dokan_product_data_tabs', function ($tabs) {
    // Add a new tab (optional) or add fields to existing "Shipping" tab
    $tabs['shipping']['fields']['shipping_description'] = array(
        'label' => __('Shipping Description', 'your-textdomain'),
        'type' => 'textarea',
        'value' => '', // Will be prefilled automatically
        'placeholder' => __('Enter shipping description', 'your-textdomain'),
        'description' => __('This will be synced to product meta.', 'your-textdomain')
    );
    $tabs['general']['fields']['admin_commission'] = array(
        'label' => __('Admin Commission %', 'your-textdomain'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.01',
            'min' => '0'
        ),
        'value' => '',
        'placeholder' => __('Enter commission %', 'your-textdomain'),
        'description' => __('Commission for this product.', 'your-textdomain')
    );
    return $tabs;
}, 10, 1);
add_filter('dokan_product_data_fields', function ($fields, $post_id) {
    if ($post_id) {
        $fields['shipping_description']['value'] = get_post_meta($post_id, '_shipping_description', true);
        $fields['admin_commission']['value'] = get_post_meta($post_id, '_admin_commission', true);
    }
    return $fields;
}, 10, 2);
add_action('init', function () {
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    $uri = trim($_SERVER['REQUEST_URI'], '/');
    // Match: product-category/{slug}/
    if (preg_match('#^product-category/([^/]+)/?$#', $uri, $matches)) {
        $category = sanitize_text_field($matches[1]);
        wp_redirect(
            home_url('/products/?category=' . $category),
            301
        );
        exit;
    }
    if (preg_match('#^amazon-category/([^/]+)/?$#', $uri, $matches)) {
        $category = sanitize_text_field($matches[1]);
        wp_redirect(
            home_url('/products/?category=' . $category),
            301
        );
        exit;
    }
});
// shipping description and admin commission save
// Display Shipping Description field in Dokan product form
add_action('dokan_new_product_after_product_tags', 'add_shipping_description_dokan');
add_action('dokan_product_edit_after_product_tags', 'add_shipping_description_dokan');
add_action('dokan_new_product_after_product_tags', 'add_shipping_description_dokan');
add_action('dokan_product_edit_after_product_tags', 'add_shipping_description_dokan');
function add_shipping_description_dokan($post_id = 0)
{
    $value = '';
    if ($post_id) {
        $value = get_field('shipping_description', $post_id);
    }
    echo '<div class="dokan-form-group">';
    echo '<label for="shipping_description" class="dokan-form-label">';
    echo '<strong>' . esc_html__('Shipping Description', 'text-domain') . '</strong>';
    echo '</label>';
    wp_editor(
        $value,
        'shipping_description',
        [
            'textarea_name' => 'shipping_description',
            'media_buttons' => false,
            'teeny' => true,
            'textarea_rows' => 8,
            'quicktags' => true,
        ]
    );
    echo '</div>'; // ✅ THIS WAS MISSING
}
// Save Shipping Description field
add_action('dokan_new_product_added', 'save_shipping_description_dokan', 10, 2);
add_action('dokan_product_updated', 'save_shipping_description_dokan', 10, 2);
function save_shipping_description_dokan($product_id, $data)
{
    if (isset($_POST['shipping_description'])) {
        update_field(
            'shipping_description',
            wp_kses_post($_POST['shipping_description']),
            $product_id
        );
    }
}
// Best selling
// Add Best Seller Priority field
add_filter('manage_edit-product_columns', function ($columns) {
    $columns['best_seller_priority'] = 'Best Seller';
    return $columns;
});
add_action('manage_posts_custom_column', function ($column, $post_id) {
    if ($column === 'best_seller_priority') {
        $value = get_post_meta($post_id, '_best_seller_priority', true);
    ?>
        <input type="number" class="best-seller-input" data-product-id="<?php echo esc_attr($post_id); ?>"
            value="<?php echo esc_attr($value); ?>" min="1" step="1" style="width:70px;" />
        <?php
    }
}, 10, 2);
add_action('wp_ajax_save_best_seller_priority', function () {
    if (!current_user_can('edit_products')) {
        wp_send_json_error();
    }
    $product_id = intval($_POST['product_id']);
    $value = intval($_POST['value']);
    update_post_meta($product_id, '_best_seller_priority', $value);
    wp_send_json_success();
});
add_action('admin_footer', function () {
    $screen = get_current_screen();
    if ($screen->id !== 'edit-product')
        return;
    ?>
        <script>
        jQuery(document).on('change', '.best-seller-input', function() {
            let input = jQuery(this);
            jQuery.post(ajaxurl, {
                action: 'save_best_seller_priority',
                product_id: input.data('product-id'),
                value: input.val()
            });
        });
        </script>
        <?php
});
add_filter('manage_edit-product_sortable_columns', function ($columns) {
    $columns['best_seller_priority'] = 'best_seller_priority';
    return $columns;
});
add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query())
        return;
    if ($query->get('orderby') === 'best_seller_priority') {
        $query->set('meta_key', '_best_seller_priority');
        $query->set('orderby', 'meta_value_num');
    }
});
add_filter('posts_clauses', 'order_products_by_best_seller_priority', 10, 2);
function order_products_by_best_seller_priority($clauses, $query)
{
    global $wpdb;
    if (!$query->get('best_seller_order')) {
        return $clauses;
    }
    $clauses['join'] .= "
        LEFT JOIN {$wpdb->postmeta} AS bspm
        ON {$wpdb->posts}.ID = bspm.post_id
        AND bspm.meta_key = '_best_seller_priority'
    ";
    $clauses['orderby'] = "
        CASE
            WHEN bspm.meta_value IS NULL OR bspm.meta_value = '' THEN 1
            ELSE 0
        END ASC,
        CAST(bspm.meta_value AS UNSIGNED) ASC,
        {$wpdb->posts}.post_date DESC
    ";
    return $clauses;
}
function current_year_shortcode()
{
    return date('Y');
}
add_shortcode('year', 'current_year_shortcode');
// category name in product listing page title
function dynamic_category_title_shortcode()
{
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'Everything';
    // Format category (capitalize first letter)
    $category = ucfirst($category);
    return '<strong>' . $category . ' </strong>';
}
add_shortcode('dynamic_category_title', 'dynamic_category_title_shortcode');
// Recapcha for login form 
//add_action('woocommerce_register_post', 'custom_recaptcha_validation', 10, 3);
function custom_recaptcha_validation($username, $email, $errors)
{
    if (empty($_POST['g-recaptcha-response'])) {
        $errors->add('recaptcha_error', __('Please verify that you are not a robot.', 'woocommerce'));
        return;
    }
    // $secret_key = '6Lc1l4QsAAAAADkcgGUM1J9RQqQveZztuAenPLPE';
    $secret_key = '6LcIAXUsAAAAANAGWvZ-ZTkwzUtqhjGwdbDX-Z06';
    $response = $_POST['g-recaptcha-response'];
    $verify = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$response");
    $body = wp_remote_retrieve_body($verify);
    $result = json_decode($body);
    // if (!$result->success) {
    //  $errors->add('recaptcha_error', __('reCAPTCHA verification failed. Try again.', 'woocommerce'));
    // }
}
//  product wise GST code for checkout page 17-4-26
// ✅ Get GST rate from tax class
function get_product_gst_rate($product)
{
    $tax_class = $product->get_tax_class();
    if (preg_match('/(\d+)/', $tax_class, $matches)) {
        return floatval($matches[1]);
    }
    return 0;
}
// ✅ Show GST breakdown in cart & checkout (correct for inclusive tax)
// add_filter('woocommerce_get_item_data', 'custom_display_gst_breakdown', 10, 2);
// function custom_display_gst_breakdown($item_data, $cart_item)
// {
//  if (empty($cart_item['data']))
//      return $item_data;
//  $product = $cart_item['data'];
//  $gst_rate = get_product_gst_rate($product);
//  if ($gst_rate > 0) {
//      // WooCommerce gives subtotal INCLUDING TAX
//      $line_total = $cart_item['line_total'] + $cart_item['line_tax'];
//      // Reverse calculate GST (correct way)
//      $base_price = $line_total / (1 + ($gst_rate / 100));
//      $gst_amount = $line_total - $base_price;
//      $item_data[] = array(
//          'key' => 'Base Price',
//          'value' => wc_price($base_price),
//      );
//      $item_data[] = array(
//          'key' => 'GST (' . $gst_rate . '%)',
//          'value' => wc_price($gst_amount),
//      );
//  }
//  return $item_data;
// }
// // ✅ Improve product price display (optional)
// add_filter('woocommerce_cart_item_price', 'custom_cart_price_with_gst_note', 10, 3);
// function custom_cart_price_with_gst_note($price_html, $cart_item, $cart_item_key)
// {
//  $product = $cart_item['data'];
//  $gst_rate = get_product_gst_rate($product);
//  if ($gst_rate > 0) {
//      $price_html .= '<br><small style="color:#666;">(Incl. GST ' . $gst_rate . '%)</small>';
//  }
//  return $price_html;
// }
// ✅ Show GST info in checkout line item (clean)
// add_filter('woocommerce_checkout_cart_item_quantity', 'custom_checkout_gst_info', 10, 3);
// function custom_checkout_gst_info($quantity_html, $cart_item, $cart_item_key)
// {
//  $product = $cart_item['data'];
//  $gst_rate = get_product_gst_rate($product);
//  if ($gst_rate > 0) {
//      $line_total = $cart_item['line_total'] + $cart_item['line_tax'];
//      $base_price = $line_total / (1 + ($gst_rate / 100));
//      $gst_amount = $line_total - $base_price;
//      $quantity_html .= '<br><small style="color:#666;">GST (' . $gst_rate . '%): ' . wc_price($gst_amount) . '</small>';
//  }
//  return $quantity_html;
// }
// category heading in product listing page
function modernlifafa_dynamic_products_heading()
{
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category_value = sanitize_text_field($_GET['category']);
        // Try slug first
        $term = get_term_by('slug', $category_value, 'product_cat');
        // Fallback: try by name
        if (!$term) {
            $term = get_term_by('name', str_replace('-', ' ', $category_value), 'product_cat');
        }
        if ($term && !is_wp_error($term)) {
            $output = '<h2 class="cate-custom-heading">' . esc_html($term->name) . '</h2>';
            // ✅ Add description here
            if (!empty($term->description)) {
                $output .= '<p class="cate-custom-subheading">' . wp_kses_post($term->description) . '</p>';
            }
            return $output;
        } else {
            return '<h2 class="cate-custom-heading">' . esc_html(ucwords(str_replace('-', ' ', $category_value))) . '</h2>';
        }
    } else {
        return '
            <h2 class="cate-custom-heading">Everything You Love, All in One Place</h2>
            <p class="cate-custom-subheading">
                Browse the full collection of handpicked gifts to build a registry that’s uniquely you—practical, personal, and full of joy.
            </p>
        ';
    }
}
add_shortcode('dynamic_products_heading', 'modernlifafa_dynamic_products_heading');
// remove order note field in checkout page
add_filter('woocommerce_checkout_fields', 'remove_order_notes_field');
function remove_order_notes_field($fields)
{
    unset($fields['order']['order_comments']);
    return $fields;
}
add_filter('woocommerce_enable_order_notes_field', '__return_false');
add_filter('woocommerce_cart_totals_get_fees_from_cart_taxes', '__return_empty_array');
// add_filter('woocommerce_get_order_item_totals', 'custom_reorder_checkout_rows', 10, 2);
// function custom_reorder_checkout_rows($total_rows, $order)
// {
//  $new_rows = [];
//  // 1. Move ALL shipping rows first
//  foreach ($total_rows as $key => $row) {
//      if (strpos($key, 'shipping') !== false) {
//          $new_rows[$key] = $row;
//      }
//  }
//  // 2. Then subtotal
//  if (isset($total_rows['cart_subtotal'])) {
//      $new_rows['cart_subtotal'] = $total_rows['cart_subtotal'];
//  }
//  // 3. Then everything else (including TOTAL last)
//  foreach ($total_rows as $key => $row) {
//      if (strpos($key, 'shipping') === false && $key !== 'cart_subtotal') {
//          $new_rows[$key] = $row;
//      }
//  }
//  return $new_rows;
// }
// add_filter( 'woocommerce_cart_totals_order_total_html', 'show_shipping_separately', 10, 1 );
// function show_shipping_separately( $value ) {
//     if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) {
//         echo '<tr class="shipping">
//                 <th>Shipping</th>
//                 <td>' . wc_price( WC()->cart->get_shipping_total() ) . '</td>
//               </tr>';
//     }
//     return $value;
// }
// cross sell product enquueue carousel js css in product details page
function enqueue_slick_slider_assets()
{
    // Slick CSS
    wp_enqueue_style(
        'slick-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css',
        array(),
        '1.8.1'
    );
    // Optional theme CSS (recommended)
    wp_enqueue_style(
        'slick-theme-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css',
        array('slick-css'),
        '1.8.1'
    );
    // Slick JS (depends on jQuery)
    wp_enqueue_script(
        'slick-js',
        'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js',
        array('jquery'),
        '1.8.1',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider_assets');
// cross sell product in product details page
add_shortcode('custom_cross_sells', function () {
    if (!is_product())
        return '';
    $product = wc_get_product(get_the_ID());
    if (!$product)
        return '';
    $cross_sells = $product->get_cross_sell_ids();
    if (empty($cross_sells))
        return '';
    ob_start();
    echo '<div class="custom-cross-sell-slider cross-sell-products-cls-adding">';
    echo '<h2>Related Products</h2>';
    echo '<div class="cross-sell-carousel">';
    foreach ($cross_sells as $id) {
        echo '<div class="item">';
        echo do_shortcode('[products ids="' . $id . '" columns="1"]');
        echo '</div>';
    }
    echo '</div></div>';
    return ob_get_clean();
});
add_filter('woocommerce_cart_shipping_method_full_label', 'hide_zero_flat_rate_label', 10, 2);
function hide_zero_flat_rate_label($label, $method)
{
    // Check if method is flat rate
    if ($method->method_id === 'flat_rate') {
        // Get cost
        $cost = $method->cost;
        // If no cost or zero → hide label
        if (empty($cost) || $cost == 0) {
            return ''; // remove label completely
        }
    }
    return $label;
}
add_action('wp_footer', function () {
    if (!is_checkout()) return;
    $countries = WC()->countries->get_countries();
    $states = WC()->countries->get_states();
?>
        <script>
        var wc_countries = <?php echo json_encode($countries); ?>;
        var wc_states = <?php echo json_encode($states); ?>;
        </script>
        <?php
});
add_filter('woocommerce_countries_shipping_countries', function ($countries) {
    return array('IN' => 'India');
});
function custom_viewport_meta()
{
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">';
}
add_action('wp_head', 'custom_viewport_meta');
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta, $item) {
    foreach ($formatted_meta as $key => $meta) {
        if ($meta->key === '_dokan_commission_source') {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}, 10, 2);
add_filter('woocommerce_hidden_order_itemmeta', function ($hidden_meta) {
    $hidden_meta[] = '_dokan_commission_source';
    return $hidden_meta;
});
add_action('wp_head', function () {
?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-CLVR193KPH"></script>
        <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-CLVR193KPH');
        </script>
        <?php
});
function registry_auto_refresh_script()
{
    if (is_page('wishlist') && is_user_logged_in()) {
    ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 15 minutes = 900000 ms
            setInterval(function() {
                window.location.reload();
            }, 900000);
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'registry_auto_refresh_script');
// This code is for making send order details emails from background
add_action('add_meta_boxes', function () {
    $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')
        ? wc_get_page_screen_id('shop-order') // HPOS
        : 'shop_order';
    add_meta_box(
        'custom_wc_email_box',
        'Send Customer Email',
        'custom_wc_email_box_html',
        $screen,
        'side',
        'high'
    );
});
function custom_wc_email_box_html($post)
{
    $order_id = $post->ID;
    $url = wp_nonce_url(
        admin_url('admin-post.php?action=send_wc_email&order_id=' . $order_id),
        'send_wc_email_' . $order_id
    );
    echo '<a href="' . $url . '" class="button button-primary" style="width:100%;">
    Send Order Details Email
    </a>';
}
add_action('admin_post_send_wc_email', 'send_wc_email_handler');
function send_wc_email_handler()
{
    if (empty($_GET['order_id'])) {
        wp_die('Missing order ID');
    }
    $order_id = intval($_GET['order_id']);
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_die('Invalid order');
    }
    $status = 'error'; // default
    try {
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();
        $email = $emails['WC_Email_Customer_Processing_Order'];
        $email->object    = $order;
        $email->recipient = $order->get_billing_email();
        $email->enabled   = 'yes';
        global $post;
        $post = get_post($order_id);
        setup_postdata($post);
        $content = $email->get_content();
        if (!empty($content)) {
            $sent = wp_mail(
                $email->recipient,
                $email->get_subject(),
                $content,
                array('Content-Type: text/html; charset=UTF-8')
            );
            if ($sent) {
                $status = 'success';
            }
        }
        wp_reset_postdata();
    } catch (Throwable $e) {
        error_log('Email error: ' . $e->getMessage());
        $status = 'error';
    }
    // ✅ Redirect with status
    wp_redirect(
        admin_url('post.php?post=' . $order_id . '&action=edit&email_status=' . $status)
    );
    exit;
}
add_action('admin_notices', function () {
    if (!isset($_GET['email_status'])) {
        return;
    }
    if ($_GET['email_status'] === 'success') {
        echo '<div class="notice notice-success is-dismissible">
                <p>Email sent successfully.</p>
              </div>';
    }
    if ($_GET['email_status'] === 'error') {
        echo '<div class="notice notice-error is-dismissible">
                <p>Failed to send email.</p>
              </div>';
    }
});
// For making send email in background on cron for contact from7
// Disable default CF7 email
// Disable CF7 default email
add_filter('wpcf7_skip_mail', '__return_true');
// Trigger BEFORE mail is sent
add_action('wpcf7_before_send_mail', 'cf7_schedule_background_email');
function cf7_schedule_background_email($contact_form)
{
    error_log('CF7 before_send_mail triggered');
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        error_log('❌ No submission object');
        return;
    }
    $data = $submission->get_posted_data();
    error_log('📩 Form data: ' . print_r($data, true));
    // Schedule cron job
    wp_schedule_single_event(time() + 5, 'cf7_send_email_async', [$data]);
    error_log('✅ WP Cron scheduled');
}
add_action('cf7_send_email_async', 'cf7_send_email_function', 10, 1);
function cf7_send_email_function($data = [])
{
    error_log('🚀 Cron email triggered');
    if (empty($data)) {
        error_log('❌ No data received in cron');
        return;
    }
    // Force sender
    add_filter('wp_mail_from', function () {
        return 'noreply@modernlifafa.com';
    });
    add_filter('wp_mail_from_name', function () {
        return 'Modern Lifafa';
    });
    $to = get_option('admin_email');
    $subject = 'New Contact Form Submission';
    $message = '';
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        $message .= ucfirst($key) . ': ' . $value . "\n";
    }
    $sent = wp_mail($to, $subject, $message);
    if ($sent) {
        error_log('✅ Email sent successfully');
    } else {
        error_log('❌ Email FAILED');
    }
}
// add_action('init', function () {
//     if (!wp_next_scheduled('test_cron_event')) {
//         wp_schedule_single_event(time() + 10, 'test_cron_event');
//         error_log('Test cron scheduled');
//     }
// });
// add_action('test_cron_event', function () {
//     error_log('📧 Trying to send email...');
//     add_filter('wp_mail_from', function () {
//         return 'noreply@modernlifafa.com'; // MUST be your domain email
//     });
//     add_filter('wp_mail_from_name', function () {
//         return 'Modern Lifafa';
//     });
//     $to = 'nenshi.patel@cmarix.com'; // test with real inbox
//     $subject = 'Cron Test Email';
//     $message = 'Cron is working';
//     $sent = wp_mail($to, $subject, $message);
//     if ($sent) {
//         error_log('✅ Email sent successfully');
//     } else {
//         error_log('❌ Email sending FAILED');
//     }
// });
// // 1. Register the custom columns in the WooCommerce product exporter
// add_filter( 'woocommerce_product_export_column_names', 'add_dokan_custom_export_columns' );
// function add_dokan_custom_export_columns( $columns ) {
//     $columns['dokan_brand']             = 'Brand';
//     $columns['dokan_vendor_name']       = 'Vendor Name';
//     $columns['dokan_admin_commission']  = 'Admin Commission %';
//     $columns['dokan_vendor_commission'] = 'Vendor Commission %';
//     return $columns;
// }
// // 2. Connect the data to the columns during export
// add_filter( 'woocommerce_product_export_product_column_dokan_brand', 'export_dokan_brand_data', 10, 2 );
// add_filter( 'woocommerce_product_export_product_column_dokan_vendor_name', 'export_dokan_vendor_name_data', 10, 2 );
// add_filter( 'woocommerce_product_export_product_column_dokan_admin_commission', 'export_dokan_admin_commission_data', 10, 2 );
// add_filter( 'woocommerce_product_export_product_column_dokan_vendor_commission', 'export_dokan_vendor_commission_data', 10, 2 );
// // Fetch Brand data from 'brand' taxonomy
// function export_dokan_brand_data( $value, $product ) {
//     $terms = wp_get_post_terms( $product->get_id(), 'brand', array( 'fields' => 'names' ) );
//     return ! is_wp_error( $terms ) && ! empty( $terms ) ? implode( ', ', $terms ) : '';
// }
// // Fetch Vendor Name data
// function export_dokan_vendor_name_data( $value, $product ) {
//     $author_id = get_post_field( 'post_author', $product->get_id() );
//     $vendor    = dokan()->vendor->get( $author_id );
//     return $vendor && ! is_wp_error( $vendor ) ? $vendor->get_shop_name() : '';
// }
// // Fetch Admin Commission %
// function export_dokan_admin_commission_data( $value, $product ) {
//     $product_id = $product->get_id();
//     $author_id  = get_post_field( 'post_author', $product_id );
//     // Check for product-specific commission override
//     $product_commission = get_post_meta( $product_id, '_per_product_commission', true );
//     if ( ! empty( $product_commission ) ) {
//         return $product_commission . '% (Product Specific)';
//     }
//     // Fallback to vendor-specific commission
//     $vendor_commission = get_user_meta( $author_id, 'dokan_admin_percentage', true );
//     if ( ! empty( $vendor_commission ) ) {
//         return $vendor_commission . '% (Vendor Specific)';
//     }
//     // Fallback to global commission setting
//     $global_commission = dokan_get_option( 'admin_percentage', 'dokan_selling', 0 );
//     return $global_commission . '% (Global)';
// }
// // Fetch Vendor Commission %
// function export_dokan_vendor_commission_data( $value, $product ) {
//     $product_id = $product->get_id();
//     $author_id  = get_post_field( 'post_author', $product_id );
//     // Check product specific first
//     $product_commission = get_post_meta( $product_id, '_per_product_commission', true );
//     if ( ! empty( $product_commission ) ) {
//         return ( 100 - (float) $product_commission ) . '%';
//     }
//     // Check vendor specific next
//     $vendor_commission = get_user_meta( $author_id, 'dokan_admin_percentage', true );
//     if ( ! empty( $vendor_commission ) ) {
//         return ( 100 - (float) $vendor_commission ) . '%';
//     }
//     // Default to global split
//     $global_commission = dokan_get_option( 'admin_percentage', 'dokan_selling', 0 );
//     return ( 100 - (float) $global_commission ) . '%';
// }
// new code for export products
// 1. Register the custom columns in the WooCommerce product exporter
add_filter('woocommerce_product_export_column_names', 'add_dokan_custom_export_columns');
function add_dokan_custom_export_columns($columns)
{
    $columns['dokan_brand']             = 'Brand';
    $columns['dokan_vendor_name']       = 'Vendor Name';
    $columns['dokan_admin_commission']  = 'Admin Commission %';
    $columns['dokan_vendor_commission'] = 'Vendor Commission %';
    return $columns;
}
// 2. Connect the data to the columns during export
add_filter('woocommerce_product_export_product_column_dokan_brand', 'export_dokan_brand_data', 10, 2);
add_filter('woocommerce_product_export_product_column_dokan_vendor_name', 'export_dokan_vendor_name_data', 10, 2);
add_filter('woocommerce_product_export_product_column_dokan_admin_commission', 'export_dokan_admin_commission_data', 10, 2);
add_filter('woocommerce_product_export_product_column_dokan_vendor_commission', 'export_dokan_vendor_commission_data', 10, 2);
// Fetch Brand data from 'brand' taxonomy
function export_dokan_brand_data($value, $product)
{
    $terms = wp_get_post_terms($product->get_id(), 'brand', array('fields' => 'names'));
    return ! is_wp_error($terms) && ! empty($terms) ? implode(', ', $terms) : '';
}
// Fetch Vendor Name data
function export_dokan_vendor_name_data($value, $product)
{
    $author_id = get_post_field('post_author', $product->get_id());
    $vendor    = dokan()->vendor->get($author_id);
    return $vendor && ! is_wp_error($vendor) ? $vendor->get_shop_name() : '';
}
// Fetch Admin Commission %
// Fetch Admin Commission
function export_dokan_admin_commission_data($value, $product)
{
    $product_id = $product->get_id();
    $author_id  = get_post_field('post_author', $product_id);
    /*
     * 1. PRODUCT-SPECIFIC COMMISSION
     */
    $product_commission = get_post_meta(
        $product_id,
        '_per_product_admin_commission',
        true
    );
    $product_type = get_post_meta(
        $product_id,
        '_per_product_admin_commission_type',
        true
    );
    $product_fee = get_post_meta(
        $product_id,
        '_per_product_admin_additional_fee',
        true
    );
    if ($product_commission !== '') {
        // Admin Commission is displayed as percentage
        $output = $product_commission . '%';
        // Add fixed ₹ amount if present
        if ($product_fee !== '' && (float) $product_fee != 0) {
            $output .= ' + ₹' . $product_fee;
        }
        return $output;
    }
    /*
     * 2. DOKAN VENDOR COMMISSION
     */
    $vendor_commission = get_user_meta(
        $author_id,
        'dokan_admin_percentage',
        true
    );
    $vendor_fee = get_user_meta(
        $author_id,
        'dokan_admin_additional_fee',
        true
    );
    if ($vendor_commission !== '') {
        $output = $vendor_commission . '%';
        // Add fixed ₹ amount if present
        if ($vendor_fee !== '' && (float) $vendor_fee != 0) {
            $output .= ' + ₹' . $vendor_fee;
        }
        return $output;
    }
    /*
     * 3. NOTHING FOUND
     */
    return '';
}
// Fetch Vendor Commission
function export_dokan_vendor_commission_data($value, $product)
{
    $product_id = $product->get_id();
    $author_id  = get_post_field('post_author', $product_id);
    /*
     * --------------------------------------------------
     * 1. PRODUCT-SPECIFIC COMMISSION
     * --------------------------------------------------
     */
    $product_commission = get_post_meta(
        $product_id,
        '_per_product_admin_commission',
        true
    );
    $product_type = get_post_meta(
        $product_id,
        '_per_product_admin_commission_type',
        true
    );
    if ($product_commission !== '') {
        // Only calculate vendor percentage
        // if admin commission is percentage based.
        if ($product_type === 'percentage') {
            return (100 - (float) $product_commission) . '%';
        }
        // Fixed admin commission has no
        // directly corresponding vendor percentage.
        return '';
    }
    /*
     * --------------------------------------------------
     * 2. DOKAN VENDOR COMMISSION
     * --------------------------------------------------
     */
    $vendor_commission = get_user_meta(
        $author_id,
        'dokan_admin_percentage',
        true
    );
    if ($vendor_commission !== '') {
        return (100 - (float) $vendor_commission) . '%';
    }
    /*
     * --------------------------------------------------
     * 3. NOTHING FOUND
     * --------------------------------------------------
     */
    return '';
}
// upi id
/**
 * Modern Lifafa - Save Couple UPI ID
 *
 * Database:
 * wp_usermeta
 *
 * Meta key:
 * _ml_upi_id
 */
add_action('wp_ajax_ml_save_upi_id', 'ml_save_upi_id');
function ml_save_upi_id()
{
    // Verify nonce
    check_ajax_referer('ml_upi_nonce', 'nonce');
    // Require login
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'Please log in to save your UPI ID.'
        ]);
    }
    // Current logged-in couple
    $user_id = get_current_user_id();
    // Get UPI ID
    $upi_id = isset($_POST['upi_id'])
        ? sanitize_text_field(
            wp_unslash($_POST['upi_id'])
        )
        : '';
    // Get confirmation UPI ID
    $confirm_upi_id = isset($_POST['confirm_upi_id'])
        ? sanitize_text_field(
            wp_unslash($_POST['confirm_upi_id'])
        )
        : '';
    // Normalize values
    $upi_id = strtolower(trim($upi_id));
    $confirm_upi_id = strtolower(trim($confirm_upi_id));
    // Required validation
    if (empty($upi_id) || empty($confirm_upi_id)) {
        wp_send_json_error([
            'message' => 'Please enter and confirm your UPI ID.'
        ]);
    }
    // Match validation
    if ($upi_id !== $confirm_upi_id) {
        wp_send_json_error([
            'message' => 'UPI IDs do not match.'
        ]);
    }
    // Basic UPI format validation
    if (!preg_match(
        '/^[a-zA-Z0-9._-]{2,256}@[a-zA-Z0-9.-]{2,64}$/',
        $upi_id
    )) {
        wp_send_json_error([
            'message' => 'Please enter a valid UPI ID.'
        ]);
    }
    // Save UPI ID to user meta
    $saved = update_user_meta(
        $user_id,
        '_ml_upi_id',
        $upi_id
    );
    // Verify saved value
    $saved_upi_id = get_user_meta(
        $user_id,
        '_ml_upi_id',
        true
    );
    if ($saved_upi_id !== $upi_id) {
        wp_send_json_error([
            'message' => 'Unable to save UPI ID. Please try again.'
        ]);
    }
    // Success response
    wp_send_json_success([
        'message' => 'UPI ID saved successfully!',
        'upi_id'  => $saved_upi_id
    ]);
}
/**
 * Modern Lifafa - Display UPI ID in Admin User Profile
 */
add_action(
    'show_user_profile',
    'ml_admin_show_upi_details'
);
add_action(
    'edit_user_profile',
    'ml_admin_show_upi_details'
);
function ml_admin_show_upi_details($user)
{
    // Get couple UPI ID
    $upi_id = get_user_meta(
        $user->ID,
        '_ml_upi_id',
        true
    );
    ?>
        <h2>UPI Details</h2>
        <table class="form-table">
            <tr>
                <th>
                    <label>Couple UPI ID</label>
                </th>
                <td>
                    <?php if (!empty($upi_id)) : ?>
                    <input type="text" class="regular-text" value="<?php echo esc_attr($upi_id); ?>" readonly>
                    <?php else : ?>
                    <p>
                        UPI ID has not been added yet.
                    </p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
}
/** Image metadata diagnostics for an external registry product; no remote requests. */
function mlf_registry_image_diagnostics($post_id)
{
    $post_id = absint($post_id);
    return [
        'post_id' => $post_id,
        'thumbnail_id' => (int) get_post_thumbnail_id($post_id),
        'external_image' => (string) get_post_meta($post_id, '_ml_external_image', true),
        'import_error' => (string) get_post_meta($post_id, '_ml_image_error', true),
    ];
}
/**
 * Get product data from a Shopify storefront.
 * Supports product variants and prices in minor currency units.
 */
function mlf_get_shopify_product_data($url)
{
    $parts = wp_parse_url($url);
    if (
        empty($parts['scheme']) ||
        empty($parts['host']) ||
        empty($parts['path'])
    ) {
        return [];
    }
    if (!preg_match(
        '#^(/(?:[a-z]{2}(?:-[a-z]{2})?/)?products/[^/]+)#i',
        $parts['path'],
        $match
    )) {
        return [];
    }
    $endpoint = $parts['scheme'] . '://' .
        $parts['host'] . $match[1] . '.js';
    if (!wp_http_validate_url($endpoint)) {
        return [];
    }
    $response = wp_safe_remote_get($endpoint, [
        'timeout' => 15,
        'redirection' => 3,
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);
    if (
        is_wp_error($response) ||
        wp_remote_retrieve_response_code($response) !== 200
    ) {
        return [];
    }
    $data = json_decode(
        wp_remote_retrieve_body($response),
        true
    );
    if (
        !is_array($data) ||
        empty($data['title']) ||
        empty($data['variants'])
    ) {
        return [];
    }
    // Get selected variant from the original URL.
    $query = [];
    parse_str($parts['query'] ?? '', $query);
    $variant_id = sanitize_text_field(
        (string) ($query['variant'] ?? '')
    );
    $selected = null;
    foreach ($data['variants'] as $variant) {
        if (
            (string) ($variant['id'] ?? '') === $variant_id
        ) {
            $selected = $variant;
            break;
        }
    }
    if (!$selected) {
        $selected = $data['variants'][0];
    }
    $price = '';
    if (isset($selected['price']) && is_numeric($selected['price'])) {
        $price = (string) (
            (float) $selected['price'] / 100
        );
    }
    $image = '';
    if (!empty($selected['featured_image'])) {
        $featured = $selected['featured_image'];
        if (is_array($featured)) {
            $image = $featured['src'] ?? '';
        } elseif (is_string($featured)) {
            $image = $featured;
        }
    }
    if (!$image && !empty($data['images'][0])) {
        $first_image = $data['images'][0];
        $image = is_array($first_image)
            ? ($first_image['src'] ?? '')
            : $first_image;
    }
    return [
        'title' => sanitize_text_field($data['title']),
        'price' => $price,
        'image' => mlf_import_image_url($image, $url),
        'description' => wp_kses_post(
            $data['description'] ?? ''
        ),
    ];
}
// Fix Registry success popup message for all products.
add_action('wp_footer', function () {
?>
        <script>
        jQuery(function($) {
            const successMessage =
                'Your gift was successfully added to your Registry. ' +
                'You can edit or add more gifts at any time!';
            const popupMessage = document.querySelector(
                '.success-popup .popup-message'
            );
            if (!popupMessage) {
                return;
            }

            function fixRegistryMessage() {
                if (popupMessage.textContent.trim() !== successMessage) {
                    popupMessage.textContent = successMessage;
                }
            }
            // Set initial message.
            fixRegistryMessage();
            // Keep the correct message when AJAX updates the popup.
            const observer = new MutationObserver(fixRegistryMessage);
            observer.observe(popupMessage, {
                childList: true,
                characterData: true,
                subtree: true
            });
        });
        </script>
        <?php
}, 999);

/**
 * Modern Lifafa - Edit External Registry Gift
 * Supports amazon_product posts owned by the logged-in user.
 */
add_action('wp_ajax_mlf_update_external_gift', 'mlf_update_external_gift');
function mlf_update_external_gift()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in first.'], 401);
    }
        if (
        !check_ajax_referer(
            'mlf_edit_external_gift_nonce',
            'nonce',
            false
        )
    ) {
        wp_send_json_error(['message' => 'Security verification failed.'], 403);
    }
        $product_id = isset($_POST['product_id'])
        ? absint($_POST['product_id'])
        : 0;
            $product = get_post($product_id);
        if (
        !$product ||
        $product->post_type !== 'amazon_product' ||
        $product->post_status !== 'publish'
    ) {
        wp_send_json_error(['message' => 'Invalid external gift.'], 404);
    }
        // Never let a couple edit a product belonging to another user.
    if ((int) $product->post_author !== get_current_user_id()) {
        wp_send_json_error(['message' => 'You cannot edit this gift.'], 403);
    }
        $name = isset($_POST['gift_name'])
        ? sanitize_text_field(wp_unslash($_POST['gift_name']))
        : '';
            $price_input = isset($_POST['gift_price'])
        ? trim((string) wp_unslash($_POST['gift_price']))
        : '';
            if ($name === '') {
        wp_send_json_error(['message' => 'Please enter the gift name.'], 400);
    }
        if (mb_strlen($name) > 200) {
        wp_send_json_error(['message' => 'Gift name is too long.'], 400);
    }
        if (
        $price_input !== '' &&
        !preg_match('/^\d{1,9}(\.\d{1,2})?$/', $price_input)
    ) {
        wp_send_json_error(['message' => 'Enter a valid price.'], 400);
    }
        $price = $price_input === ''
        ? ''
        : wc_format_decimal($price_input, 2);
            // Validate the optional replacement image before updating details.
    $has_image = (
        isset($_FILES['gift_image']) &&
        !empty($_FILES['gift_image']['name'])
    );
        if ($has_image) {
        $file = $_FILES['gift_image'];
                if (
            !isset($file['error']) ||
            (int) $file['error'] !== UPLOAD_ERR_OK
        ) {
            wp_send_json_error(['message' => 'Image upload failed.'], 400);
        }
        if ((int) $file['size'] > 10 * 1024 * 1024) {
    wp_send_json_error(['message' => 'Image must be under 10 MB.'], 400);
}
       $allowed_types = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'webp'         => 'image/webp',
            'gif'          => 'image/gif',
            'avif'         => 'image/avif',
            'bmp'          => 'image/bmp',
            'heic'         => 'image/heic',
            'heif'         => 'image/heif',
        ];
                $check = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name'],
            $allowed_types
        );
                if (
            empty($check['type']) ||
            !in_array($check['type'], $allowed_types, true)
        ) {
            wp_send_json_error([
                'message' => 'Unsupported image format. Please upload a supported image file.'
            ], 400);
        }
                require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
                $attachment_id = media_handle_upload('gift_image', $product_id);
                if (is_wp_error($attachment_id)) {
            wp_send_json_error([
                'message' => $attachment_id->get_error_message()
            ], 400);
        }
                set_post_thumbnail($product_id, $attachment_id);
    }
        // Update the existing product title.
    $updated = wp_update_post([
        'ID'         => $product_id,
        'post_title' => $name,
    ], true);
        if (is_wp_error($updated)) {
        wp_send_json_error([
            'message' => $updated->get_error_message()
        ], 500);
    }
        // Update existing ACF price field.
    if (function_exists('update_field')) {
        update_field('price', $price, $product_id);
    } else {
        update_post_meta($product_id, 'price', $price);
    }
        // Clear cached post data.
    clean_post_cache($product_id);
        wp_send_json_success([
        'message' => 'Gift updated successfully.',
        'product_id' => $product_id,
        'name' => $name,
        'price' => $price,
        'image' => mlf_get_registry_product_image($product_id, 'full'),
    ]);
}
/**
 * Render the Edit External Gift popup on the wishlist.
 */
add_action('wp_footer', 'mlf_external_gift_edit_popup', 50);
function mlf_external_gift_edit_popup()
{
    if (!is_page('wishlist') || !is_user_logged_in()) {
        return;
    }
    ?>
        <dialog id="mlf-edit-gift-dialog" class="mlf-gift-dialog">
            <form id="mlf-edit-gift-form" enctype="multipart/form-data">
                <div class="mlf-gift-dialog-head">
                    <h3>Edit External Gift</h3>
                    <button type="button" class="mlf-gift-dialog-close" aria-label="Close">&times;</button>
                </div>
                                <input type="hidden" name="action" value="mlf_update_external_gift">
                                <input type="hidden" name="nonce" value="<?php echo esc_attr(
                       wp_create_nonce('mlf_edit_external_gift_nonce')
                   ); ?>">
                                   <input type="hidden" name="product_id" id="mlf-edit-product-id">
                                <div class="mlf-gift-field">
                    <label for="mlf-edit-gift-name">Gift Name</label>
                    <input type="text" id="mlf-edit-gift-name" name="gift_name" maxlength="200" required>
                </div>
                                <div class="mlf-gift-field">
                    <label for="mlf-edit-gift-price">Price (₹)</label>
                    <input type="number" id="mlf-edit-gift-price" name="gift_price" min="0" max="999999999" step="0.01"
                        placeholder="Enter price">
                </div>
                                <div class="mlf-gift-field">
                    <label for="mlf-edit-gift-image">Product Image</label>
                    <input type="file" id="mlf-edit-gift-image" name="gift_image" accept="image/*,.avif,.heic,.heif">
                                        <small>Upload an image (JPG, PNG, GIF, WebP, AVIF, BMP, HEIC/HEIF where supported). Maximum 10
                        MB.</small>
                </div>
                                <p id="mlf-edit-gift-message" role="status"></p>
                                <div class="mlf-gift-dialog-actions">
                    <button type="button" class="mlf-gift-dialog-close">
                        Cancel
                    </button>
                    <button type="submit" id="mlf-edit-gift-save">
                        Save Changes
                    </button>
                </div>
            </form>
        </dialog>
        <style>
            .mlf-gift-dialog{width:min(95%,480px);max-height:90vh;overflow-y:auto;border:0;border-radius:12px;padding:24px;box-shadow:0 15px 50px rgba(0,0,0,.2)}
            .mlf-gift-dialog::backdrop{background:rgba(0,0,0,.55)}
            .mlf-gift-dialog-actions,.mlf-gift-dialog-head{display:flex;justify-content:space-between;align-items:center;gap:12px}
            .mlf-gift-dialog-head h3{margin:0}
            .mlf-gift-dialog-head button{border:0;background:0 0;font-size:26px;cursor:pointer}
            .mlf-gift-field{margin:18px 0}
            .mlf-gift-field label{display:block;margin-bottom:7px;font-weight:600}
            .mlf-gift-field input{width:100%;box-sizing:border-box;padding:10px;border:1px solid #ccc;border-radius:6px}
            .mlf-gift-field small{display:block;margin-top:6px}
            .mlf-gift-dialog-actions{justify-content:flex-end;margin-top:20px}
            .mlf-gift-dialog-actions button{padding:10px 16px;border-radius:6px;cursor:pointer}
            #mlf-edit-gift-save{background:#222;color:#fff;border:1px solid #222}
            #mlf-edit-gift-message{font-size:14px}
            #wishlist-products .product-img{position:relative}
	   /* #wishlist-products .product-img .mlf-external-gift-actions{position:absolute;right:10px;bottom:10px;z-index:20;display:flex!important;flex-direction:column;align-items:center;gap:8px;width:auto;height:auto} */
#wishlist-products .product-img{position:relative}
#wishlist-products .product-img .mlf-external-gift-actions{position:absolute;right:10px;bottom:10px;z-index:20;display:flex!important;flex-direction:column;align-items:center;gap:8px;width:auto;height:auto;opacity:0;visibility:hidden;pointer-events:none;transform:translateY(8px);transition:opacity .25s ease,transform .25s ease,visibility .25s ease}
#wishlist-products .presonalised-item:focus-within .product-img .mlf-external-gift-actions,#wishlist-products .presonalised-item:hover .product-img .mlf-external-gift-actions{opacity:1;visibility:visible;pointer-events:auto;transform:translateY(0)}
@media (hover:none){
#wishlist-products .product-img .mlf-external-gift-actions{opacity:1;visibility:visible;pointer-events:auto;transform:none}
}
            #wishlist-products .product-img .mlf-external-gift-actions button{position:static!important;display:inline-flex!important;align-items:center;justify-content:center;width:40px!important;height:40px!important;min-width:40px;padding:0!important;margin:0!important;border:1px solid #ddd;border-radius:10px;background:#fff;cursor:pointer;opacity:1!important;visibility:visible!important;transform:none!important}
            #wishlist-products .mlf-edit-external-gift{color:#751651}
            #wishlist-products .mlf-external-gift-actions .remove_amazon_product{color:#f50057}
            #wishlist-products .mlf-external-gift-actions button i{display:inline-block!important;font-size:16px;line-height:1}
        </style>
        <?php
}
/**
 * Edit External Gift - Frontend JavaScript
 */
add_action('wp_footer', 'mlf_external_gift_edit_script', 60);
function mlf_external_gift_edit_script()
{
    if (!is_page('wishlist') || !is_user_logged_in()) {
        return;
    }
    ?>
        <script>
        (function() {
            'use strict';
                        const dialog = document.getElementById('mlf-edit-gift-dialog');
            const form = document.getElementById('mlf-edit-gift-form');
                        if (!dialog || !form) return;
                        const message = document.getElementById('mlf-edit-gift-message');
            const saveButton = document.getElementById('mlf-edit-gift-save');
            const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                        // Delegated click handler supports infinite-scroll products.
            document.addEventListener('click', function(event) {
                                const editButton = event.target.closest(
                    '.mlf-edit-external-gift'
                );
                                if (editButton) {
                    event.preventDefault();
                                        form.reset();
                    message.textContent = '';
                                        document.getElementById('mlf-edit-product-id').value =
                        editButton.dataset.productId || '';
                                            document.getElementById('mlf-edit-gift-name').value =
                        editButton.dataset.productName || '';
                                            document.getElementById('mlf-edit-gift-price').value =
                        editButton.dataset.productPrice || '';
                                            dialog.showModal();
                    return;
                }
                                if (
                    event.target.closest('.mlf-gift-dialog-close') &&
                    dialog.open
                ) {
                    dialog.close();
                }
            });
                        form.addEventListener('submit', async function(event) {
                event.preventDefault();
                                if (!form.reportValidity()) return;
                                const imageInput = document.getElementById('mlf-edit-gift-image');
                                if (imageInput.files.length) {
                    const image = imageInput.files[0];
                    const allowed = [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                        'image/avif',
                        'image/bmp',
                        'image/heic',
                        'image/heif'
                    ];
                                        if (!allowed.includes(image.type)) {
                        message.textContent = 'Unsupported image type.';
                        message.style.color = '#c62828';
                        return;
                    }
                                        if (image.size > 10 * 1024 * 1024) {
                        message.textContent = 'Image must be under 10 MB.';
                        message.style.color = '#c62828';
                        return;
                    }
                }
                                const formData = new FormData(form);
                                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
                message.textContent = '';
                                try {
                    const response = await fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                        const result = await response.json();
                        if (!response.ok || !result.success) {
                        throw new Error(
                            result.data?.message || 'Unable to update gift.'
                        );
                    }
                    if (result.success) {
                            // Close popup after successful save.
                        if (dialog.open) {
                            dialog.close();
                        }
                    // Refresh wishlist to display updated details.
                        window.location.reload();
                    return;
                    }
                    } catch (error) {
                    message.textContent =
                        error.message || 'Something went wrong.';
                    message.style.color = '#c62828';
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = 'Save Changes';
                }
            });
                    })();
        </script>
        <?php
}
 

/**
 * Restore Edit Gift button after wishlist filtering/sorting.
 */
add_action(
    'wp_footer',
    'mlf_restore_filtered_external_gift_edit_buttons',
    90
);

function mlf_restore_filtered_external_gift_edit_buttons()
{
    if (
        !is_page('wishlist') ||
        !is_user_logged_in() ||
        isset($_GET['original_user_id'])
    ) {
        return;
    }

    $uid = get_current_user_id();
    $allowed_ids = [];

    $saved = get_user_meta(
        $uid,
        '_personalized_amazon_products',
        true
    );

    if (is_array($saved)) {
        foreach ($saved as $item) {
            if (is_array($item) && !empty($item['product_id'])) {
                $id = absint($item['product_id']);

                if (get_post_type($id) === 'amazon_product') {
                    $allowed_ids[] = $id;
                }
            }
        }
    }

    $owned_ids = get_posts([
        'post_type'      => 'amazon_product',
        'post_status'    => 'publish',
        'author'         => $uid,
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ]);

    $allowed_ids = array_values(
        array_unique(array_merge($allowed_ids, $owned_ids))
    );
    ?>
    <script>
    (function () {
        'use strict';

        const permitted = new Set(
            <?php echo wp_json_encode(array_map('strval', $allowed_ids)); ?>
        );

        let scheduled = false;

        function restoreButtons() {
            scheduled = false;

            const root = document.getElementById('wishlist-products');
            if (!root) return;

            root.querySelectorAll('button.remove_amazon_product')
                .forEach(function (remove) {

                const id = String(
                    remove.dataset.product_id ||
                    remove.dataset.productId ||
                    ''
                );

                if (!id || !permitted.has(id)) return;

                const card = remove.closest('.presonalised-item');

                if (
                    !card ||
                    card.querySelector('.mlf-edit-external-gift')
                ) return;

                // Only external URL gifts.
                const externalLink = card.querySelector(
                    'a[href*="/amazon_product/"]'
                );

                if (!externalLink) return;

                // Do not edit purchased gifts.
                const status = card.querySelector(
                    '.product-status-wrapper .product-status'
                );

                if (
                    card.classList.contains('disabled') ||
                    (status && /purchased/i.test(status.textContent))
                ) return;

                let actions = remove.closest(
                    '.mlf-external-gift-actions'
                );

                if (!actions) {
                    actions = document.createElement('div');
                    actions.className = 'mlf-external-gift-actions';

                    remove.parentNode.insertBefore(actions, remove);
                    actions.appendChild(remove);
                }

                const name = card.querySelector('.product-title a')
                    ?.textContent?.trim() || '';

                let price = card.getAttribute('data-price') || '';

                if (!price) {
                    const priceEl = card.querySelector(
                        '.woocommerce-Price-amount222, .woocommerce-Price-amount'
                    );

                    price = priceEl
                        ? priceEl.textContent.replace(/[^0-9.]/g, '')
                        : '';
                }

                const edit = document.createElement('button');

                edit.type = 'button';
                edit.className =
                    'custom_button mlf-edit-external-gift';

                edit.title = 'Edit Gift';
                edit.setAttribute('aria-label', 'Edit Gift');

                edit.dataset.productId = id;
                edit.dataset.productName = name;
                edit.dataset.productPrice = price;

                const icon = document.createElement('i');
                icon.className = 'fas fa-edit';
                icon.setAttribute('aria-hidden', 'true');

                edit.appendChild(icon);
                actions.insertBefore(edit, remove);
            });
        }

        function scheduleRestore() {
            if (scheduled) return;

            scheduled = true;
            requestAnimationFrame(restoreButtons);
        }

        if (document.readyState === 'loading') {
            document.addEventListener(
                'DOMContentLoaded',
                restoreButtons
            );
        } else {
            restoreButtons();
        }

        // Detect changes from filters and infinite scrolling.
        new MutationObserver(scheduleRestore).observe(
            document.body,
            {
                childList: true,
                subtree: true
            }
        );
    })();
    </script>
    <?php
}

