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
            <img src="https://beta.modernlifafa.com/wp-content/uploads/2025/03/preloader.webp" alt="Rotating Image"
                class="animated-image" />
            <!-- 	<video width="100%" height="450" border="0" autoplay muted preload="auto">
                <source src="<?php // echo home_url(); ?>/wp-content/uploads/2025/03/ML_LogoAnimationInverse.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video> -->
        </div>
    <?php }
}
add_action('wp_footer', 'footer_preloader');

function filter_and_sort_wishlist()
{
    $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : '';
    $selected_slug = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
    $received_gift = isset($_POST['received_gift']) ? sanitize_text_field($_POST['received_gift']) : [];
    if (!empty($received_gift))
        $received_gift = explode(',', $received_gift);

    // 🔹 Extract taxonomy and term ID (e.g. amazon_category_137)
    // $selected_taxonomy = '';
    // $selected_category_id = '';
    // if (!empty($selected_category) && preg_match('/^([a-zA-Z0-9_]+)_(\d+)$/', $selected_category, $matches)) {
    //     $selected_taxonomy = $matches[1]; // amazon_category OR product_cat
    //     $selected_category_id = intval($matches[2]);
    // }

    global $wpdb;
    $table_name = $wpdb->prefix . 'purchased_submissions';
    $merged_items = [];
    $product_qty = get_user_meta($user_id, '_product_quantity', true);

    // ===============================================================
    // 🛒 WooCommerce Wishlist Products
    // ===============================================================
    // if ($selected_taxonomy !== 'amazon_category') { // ✅ Show only if not filtering by Amazon
    $wishlist_items = get_user_meta($user_id, '_personalized_products', true);
    if (!empty($wishlist_items) && is_array($wishlist_items)) {
        // if (!empty($selected_category_id) && $selected_taxonomy == 'product_cat') {
        //     $wishlist_items = array_filter($wishlist_items, function ($item) use ($selected_category_id) {
        //         $product_id = $item['product_id'];
        //         $woo_cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
        //         $amazon_cats = wp_get_post_terms($product_id, 'amazon_category', ['fields' => 'ids']);
        //         return in_array($selected_category_id, $woo_cats) || in_array($selected_category_id, $amazon_cats);
        //     });
        // }

        if (!empty($selected_slug)) {
            $wishlist_items = array_filter($wishlist_items, function ($item) use ($selected_slug) {
                $product_id = $item['product_id'];

                // Get slugs instead of IDs for comparison
                $woo_cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
                $amazon_cats = wp_get_post_terms($product_id, 'amazon_category', ['fields' => 'slugs']);

                return in_array($selected_slug, $woo_cats) || in_array($selected_slug, $amazon_cats);
            });
        }

        foreach ($wishlist_items as &$product_data) {
            $product_id = $product_data['product_id'];
            $product = wc_get_product($product_id);
            if (!$product)
                continue;

            $price = (float) $product->get_price();
            $product_data['display_price'] = $price;
            $product_data['source'] = 'wishlist';
        }
        unset($product_data);
        $merged_items = array_merge($merged_items, $wishlist_items);
    }

    // ===============================================================
    // 🛍️ Amazon Products
    // ===============================================================
    // if ($selected_taxonomy !== 'product_cat') { // ✅ Show only if not filtering by WooCommerce
    $amazon_items = [];
    $personalized_amazon_products = get_user_meta($user_id, '_personalized_amazon_products', true) ?? [];

    foreach ($personalized_amazon_products as $personalized_amazon_product) {
        $pro_id = $personalized_amazon_product['product_id'];
        if (get_post_type($pro_id) !== 'product') {
            $post = get_post($pro_id);
            if (!$post || $post->post_status !== 'publish')
                continue;

            // Category filter
            if (!empty($selected_slug)) {
                $woo_cats = wp_get_post_terms($pro_id, 'product_cat', ['fields' => 'slugs']);
                $amazon_cats = wp_get_post_terms($pro_id, 'amazon_category', ['fields' => 'slugs']);

                if (!in_array($selected_slug, $woo_cats) && !in_array($selected_slug, $amazon_cats)) {
                    continue;
                }
            }

            $price = floatval(preg_replace('/[^\d.]/', '', get_field('price', $pro_id)));
            $link = get_field('product_link', $pro_id);

            $amazon_items[] = [
                'product_id' => $pro_id,
                'display_price' => $price,
                'link' => $link,
                'source' => 'amazon',
            ];
        }
    }

    // Amazon posts authored by user
    $query = new WP_Query([
        'post_type' => 'amazon_product',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $product_id = get_the_ID();

        if (!empty($selected_slug)) {
            $amazon_cats = wp_get_post_terms($product_id, 'amazon_category', ['fields' => 'slugs']);
            if (!in_array($selected_slug, $amazon_cats)) {
                continue;
            }
        }

        $price = floatval(preg_replace('/[^\d.]/', '', get_field('price', $product_id)));
        $link = get_field('product_link', $product_id);

        $amazon_items[] = [
            'product_id' => $product_id,
            'display_price' => $price,
            'link' => $link,
            'source' => 'amazon',
        ];
    }
    wp_reset_postdata();

    $merged_items = array_merge($merged_items, $amazon_items);
    //}

    // ===============================================================
    // 🔹 Sort and Display
    // ===============================================================
    usort($merged_items, function ($a, $b) use ($sort_order) {
        $price_a = $a['display_price'] ?? 0;
        $price_b = $b['display_price'] ?? 0;
        return ($sort_order === 'price-asc') ? ($price_a <=> $price_b) : ($price_b <=> $price_a);
    });

    $gift_for = isset($_POST['gift_for']) ? sanitize_text_field($_POST['gift_for']) : '';
    if (!empty($merged_items)) {
        personalised_products($merged_items, $product_qty, $user_id, $received_gift, $gift_for);
    }

    wp_die();
}

add_action('wp_ajax_filter_and_sort_wishlist', 'filter_and_sort_wishlist');
add_action('wp_ajax_nopriv_filter_and_sort_wishlist', 'filter_and_sort_wishlist');

// Add column "added by" at amazon products admin page
// 1️⃣ Add new column to admin post list for amazon_product
add_filter('manage_amazon_product_posts_columns', function ($columns) {
    $columns['added_by'] = __('Added By', 'textdomain');
    return $columns;
});

// 2️⃣ Populate column content
add_action('manage_amazon_product_posts_custom_column', function ($column, $post_id) {
    if ($column === 'added_by') {
        $author_id = get_post_field('post_author', $post_id);
        $user = get_user_by('ID', $author_id);

        if (!$user) {
            echo '—';
            return;
        }

        $first_name = get_user_meta($author_id, 'first_name', true);
        $last_name = get_user_meta($author_id, 'last_name', true);
        $full_name = trim("$first_name $last_name");
        $role_names = $user->roles;

        // Determine user type
        $is_admin = in_array('administrator', $role_names);
        $display_label = $is_admin ? 'Admin' : 'Couple User';
        $profile_url = esc_url(admin_url("user-edit.php?user_id={$author_id}"));

        // Tooltip + clickable name
        echo sprintf(
            '<a href="%s" title="%s" style="text-decoration:none;" class="added-by-tooltip">%s</a>',
            $profile_url,
            esc_attr($full_name),
            esc_html($display_label)
        );
    }
}, 10, 2);

// 3️⃣ Optional: Make column sortable (by post author)
add_filter('manage_edit-amazon_product_sortable_columns', function ($columns) {
    $columns['added_by'] = 'author';
    return $columns;
});

?>
