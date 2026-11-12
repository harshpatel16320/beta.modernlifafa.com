<?php
/**
 * modern_lifafa Theme Customizer
 *
 * @package modern_lifafa
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function modern_lifafa_customize_register($wp_customize)
{
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';
    $wp_customize->get_setting('header_textcolor')->transport = 'postMessage';

    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            array(
                'selector' => '.site-title a',
                'render_callback' => 'modern_lifafa_customize_partial_blogname',
            )
        );
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            array(
                'selector' => '.site-description',
                'render_callback' => 'modern_lifafa_customize_partial_blogdescription',
            )
        );
    }
}
add_action('customize_register', 'modern_lifafa_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function modern_lifafa_customize_partial_blogname()
{
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function modern_lifafa_customize_partial_blogdescription()
{
    bloginfo('description');
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function modern_lifafa_customize_preview_js()
{
    wp_enqueue_script('modern_lifafa-customizer', get_template_directory_uri() . '/js/customizer.js', array('customize-preview'), _S_VERSION, true);
}
add_action('customize_preview_init', 'modern_lifafa_customize_preview_js');


// Fetch Orders for Given User
function get_orders_by_user($user_id)
{
    $meta_key = '_gift_for';
    $user_orders = [];

    $args = [
        'limit' => -1,
        'status' => ['completed', 'processing'],
        'return' => 'ids',
    ];
    $orders = wc_get_orders($args);

    if (!empty($orders)) {
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                foreach ($order->get_items() as $item) {
                    if ($item->get_meta($meta_key, true) == $user_id) {
                        $user_orders[] = $order_id;
                        break;
                    }
                }
            }
        }
    }
    return $user_orders;
}

add_action('wp_head', 'myplugin_ajaxurl');
function myplugin_ajaxurl()
{
    echo '<script type="text/javascript">
   var ajaxurl = "' . admin_url('admin-ajax.php') . '";
   </script>';
}

function ajax_search_results()
{
    $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    // if (!empty($search_query)) {
    $args = array(
        'posts_per_page' => 8,
        'post_type' => array('post', 'product', 'page'),
    );
    if (!empty($search_query)) {
        $args['s'] = $search_query;
    }
    $query = new WP_Query($args);
    $total_pages = $query->max_num_pages;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $url = wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()), 'thumbnail');
            $type = get_post_type();
            if ($type != 'faqs') {
                echo '<li><a href="' . get_permalink() . '">';
                if (isset($url) && !empty($url)) {
                    echo '<span class="result-img"><picture><img src="' . $url . '" alt="result image" width="30" height="30"></picture></span>';
                }
                echo '<span class="result-content">' . get_the_title() . '</span></a></li>';
            }
        }
    } else {
        echo '<span class="result-no-content">No results found.</span>';
    }
    // if($total_pages > 1){
    //     echo "<div class='search_view_more'><a href=''>View More</a></div>";
    // }
    wp_reset_postdata();
    // }
    wp_die();
}
add_action('wp_ajax_search_results', 'ajax_search_results');
add_action('wp_ajax_nopriv_search_results', 'ajax_search_results');

function morden_lifafa_pagination($links, $total_pages, $paged)
{
    echo '<ul class="pagination">';
    if ($paged > 1) {
        echo '<li class="page-item">';
        echo '<a class="page-link np-button" href="' . get_pagenum_link($paged - 1) . '" aria-label="Previous">';
        echo '<span aria-hidden="true" class="fas fa-chevron-left"></span>';
        echo '</a>';
        echo '</li>';
    } else {
        echo '<li class="page-item disabled">';
        echo '<a class="page-link np-button" href="javascript:void(0)" aria-label="Previous">';
        echo '<span aria-hidden="true" class="fas fa-chevron-left"></span>';
        echo '</a>';
        echo '</li>';
    }
    foreach ($links as $link) {
        if (strpos($link, 'current') !== false) {
            echo '<li class="page-item active">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
        } else {
            echo '<li class="page-item">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
        }
    }
    if ($paged < $total_pages) {
        echo '<li class="page-item">';
        echo '<a class="page-link np-button" href="' . get_pagenum_link($paged + 1) . '" aria-label="Next">';
        echo '<span aria-hidden="true" class="fas fa-chevron-right"></span>';
        echo '</a>';
        echo '</li>';
    } else {
        echo '<li class="page-item disabled">';
        echo '<a class="page-link np-button" href="javascript:void(0)" aria-label="Next">';
        echo '<span aria-hidden="true" class="fas fa-chevron-right"></span>';
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}

function add_dynamic_css_variable()
{
    $site_url = get_site_url(); // Get dynamic site URL
    echo "<style>:root { --site-hostname: url('$site_url'); }</style>";
}
add_action('wp_head', 'add_dynamic_css_variable');


// Footer preloader
function footer_preloader()
{
    if (!is_home() || !is_front_page()) { ?>
        <!-- Preloader Container -->
        <style>
            #custom-preloader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            }

            @keyframes rotateImage {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(-360deg);
                }
            }

            .animated-image {
                width: 150px;
                height: 150px;
                object-fit: contain;
                animation: rotateImage 3s linear infinite;
            }
        </style>

        <div id="custom-preloader" style="display:none;">
            <img src="https://beta.modernlifafa.com/wp-content/uploads/2025/03/preloader.webp"
                alt="Rotating Image" class="animated-image" />
            <!-- 	<video width="100%" height="450" border="0" autoplay muted preload="auto">
                <source src="<?php // echo home_url(); ?>/wp-content/uploads/2025/03/ML_LogoAnimationInverse.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video> -->
        </div>
    <?php }
}
add_action('wp_footer', 'footer_preloader');

// 21-03-2025

// Populate the shipping fields on the checkout page
add_filter('woocommerce_checkout_fields', 'populate_shipping_fields');

function populate_shipping_fields($fields) {
    // Set the shipping fields from the session
    if (WC()->session->get('shipping-first_name')) {
        $fields['shipping']['shipping-first_name']['default'] = WC()->session->get('shipping-first_name');
    }
    if (WC()->session->get('shipping-last_name')) {
        $fields['shipping']['shipping-last_name']['default'] = WC()->session->get('shipping-last_name');
    }
    if (WC()->session->get('shipping-address_1')) {
        $fields['shipping']['shipping-address_1']['default'] = WC()->session->get('shipping-address_1');
    }
    if (WC()->session->get('shipping-city')) {
        $fields['shipping']['shipping-city']['default'] = WC()->session->get('shipping-city');
    }
    if (WC()->session->get('shipping-postcode')) {
        $fields['shipping']['shipping-postcode']['default'] = WC()->session->get('shipping-postcode');
    }
    if (WC()->session->get('shipping-country')) {
        $fields['shipping']['shipping-country']['default'] = WC()->session->get('shipping-country');
    }
    if (WC()->session->get('shipping-state')) {
        $fields['shipping']['shipping-state']['default'] = WC()->session->get('shipping-state');
    }

    return $fields;
}

// checkout 28-12-2024
add_filter( 'woocommerce_checkout_get_value', 'set_default_shipping_address', 10, 2 );
function set_default_shipping_address( $input, $key ) {
    // echo "hello testing";
    // var_dump($key);
    // exit();
    if ( 'shipping_first_name' === $key ) {
        $input = 'John';
    }
    if ( 'shipping_last_name' === $key ) {
        $input = 'Doe'; 
    }
    if ( 'shipping_address_1' === $key ) {
        $input = '123 Default Street';
    }
    if ( 'shipping_address_2' === $key ) {
        $input = ''; 
    }
    if ( 'shipping_city' === $key ) {
        $input = 'Default City'; 
    }
    if ( 'shipping_postcode' === $key ) {
        $input = '12345'; 
    }
    if ( 'shipping_country' === $key ) {
        $input = 'US';
    }
    if ( 'shipping_state' === $key ) {
        $input = 'CA'; 
    }
    return $input;
}

add_action('woocommerce_new_order', 'update_shipping_address_based_on_checkbox', 10, 2);
function update_shipping_address_based_on_checkbox($order_id, $order) {
    $toggle_shipping = isset($_POST['namespace/toggle-shipping-address']) && $_POST['namespace/toggle-shipping-address'] === 'on';
    error_log(print_r($toggle_shipping, true));
    // if ($toggle_shipping) {
        $error = '------------called------------';
        $error1 = 'fsrggsfdgdfgf';
        foreach ($order->get_items() as $item_id => $item) {
            $gift_for_user_id = wc_get_order_item_meta($item_id, '_gift_for', true);
            error_log(print_r($gift_for_user_id, true));
            if($gift_for_user_id) {
                $shipping_address = array(
                    'first_name' => get_user_meta($gift_for_user_id, 'first_name', true),
                    'last_name'  => get_user_meta($gift_for_user_id, 'last_name', true),
                    'company'    => get_user_meta($gift_for_user_id, 'shipping_company', true),
                    'address_1'  => get_user_meta($gift_for_user_id, 'shipping-address_1', true),
                    'address_2'  => get_user_meta($gift_for_user_id, 'shipping-address_2', true),
                    'city'       => get_user_meta($gift_for_user_id, 'shipping_city', true),
                    'state'      => get_user_meta($gift_for_user_id, 'shipping_state', true),
                    'postcode'   => get_user_meta($gift_for_user_id, 'shipping_postcode', true),
                    'country'    => get_user_meta($gift_for_user_id, 'shipping_country', true),
                );
                error_log($error);
                error_log(print_r($shipping_address, true));
                foreach ($shipping_address as $key => $value) {
                    if (!empty($value)) {
                        $method_name = "set_shipping_$key";
                        if (method_exists($order, $method_name)) {
                            $order->$method_name($value);
                            error_log("Updated shipping $key: $value");
                        } else {
                            error_log("Method $method_name does not exist");
                        }
                    }
                }
                $order->save();
                break;
            }
            // error_log($error1);
            // error_log(print_r($shipping_address, true));
        }
    // }
}

// add_filter('woocommerce_checkout_fields', function ($fields) {
// 	$cart_items = WC()->cart->get_cart();

// 	foreach ($cart_items as $item) {
// 		if (isset($item['gift_for'])) {
// 			$user_id = $item['gift_for'];
// 			$fields['shipping']['shipping-address_1']['default'] = get_user_meta($user_id, 'shipping_address_1', true) ?: 'Default Address Line 1';
// 			$fields['shipping']['shipping_postcode']['default'] = get_user_meta($user_id, 'shipping_postcode', true) ?: '12345';
// 			$fields['shipping']['shipping_city']['default'] = get_user_meta($user_id, 'shipping_city', true) ?: 'Default City';
// 			$fields['shipping']['shipping_state']['default'] = get_user_meta($user_id, 'shipping_state', true) ?: 'Default State';
// 		}
// 	}

// 	return $fields;
// });

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/get-shipping-address/', array(
        'methods'  => 'POST',
        'callback' => 'custom_get_shipping_address',
        'permission_callback' => '__return_true'
    ));
});

function custom_get_shipping_address(WP_REST_Request $request) {
    $cart = WC()->cart->get_cart();
    $user_id = null;

    foreach ($cart as $cart_item) {
        if (isset($cart_item['gift_for'])) {
            $user_id = $cart_item['gift_for'];
            break;
        }
    }

    if (!$user_id) {
        return new WP_REST_Response(['success' => false, 'message' => 'No user ID found'], 400);
    }

    $shipping = array(
        'address_1' => get_user_meta($user_id, 'shipping_address_1', true),
        'postcode'  => get_user_meta($user_id, 'shipping_postcode', true),
        'city'      => get_user_meta($user_id, 'shipping_city', true),
        'state'     => get_user_meta($user_id, 'shipping_state', true),
    );

    if (empty($shipping['address_1'])) {
        return new WP_REST_Response(['success' => false, 'message' => 'User shipping address not saved. Please enter manually.'], 400);
    }

    return new WP_REST_Response(['success' => true, 'shipping' => $shipping], 200);
}


?>
