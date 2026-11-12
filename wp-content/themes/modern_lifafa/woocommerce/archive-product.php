<?php
defined('ABSPATH') || exit;
get_header(); ?>

<?php
$queried_object = get_queried_object();
$taxonomy = $queried_object->taxonomy ?? '';
$hide_category_filter = ($taxonomy === 'product_cat') ? 'style="display:none;"' : '';
$hide_brand_filter = ($taxonomy === 'brand') ? 'style="display:none;"' : '';
?>
<section class="our-products-section listing-inner">
    <input type="hidden" class="qty" value="1">
    <div class="container">
        <?php $term = get_queried_object(); ?>
        <h2 class="section-title"><?php echo $term->name; ?></h2>
        <div class="filter-wrap">
            <div class="filter-inner category--">
                <div class="backto-wishlist-btn">
                    <?php if (is_user_logged_in()) { ?>
                        <a href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>"
                            class="back_wishlist custom_button desktop">Back To Wishlist</a>
                        <a href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>" class="back_wishlist mobile">
                            <!-- <img class="BackwhishIcon"
                                src="<?php echo get_template_directory_uri(); ?>/assets/images/back-to-top-svg.webp"
                                alt="back-to-top" width="24" height="24">  -->
                            Back To Wishlist
                        </a>
                        <div class="filter-box clear_filter_mobile">
                            <input type="button" class="custom_button secondary_button clear_filter"
                                value="Clear Filters" />
                        </div>
                    <?php } ?>
                    <div class="back_to_wishlist_button">
                        <a class="back_wishlist mobile"
                            href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>">Back
                            to wishlist</a>
                    </div>
                </div>
                <div class="filters">
                    <!-- Category Filter (Hidden on category pages) -->
                    <?php
                    // $terms = get_terms(array(
                    //     'taxonomy' => 'product_cat',
                    //     'hide_empty' => false,
                    //     'order' => 'ASC',
                    // ));
                    
                    $product_terms = get_terms([
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                        'best_seller_order' => true,
                    ]);

                    $amazon_terms = get_terms([
                        'taxonomy' => 'amazon_category',
                        'hide_empty' => true,
                        'best_seller_order' => true,
                    ]);

                    $all_terms = array_merge(
                        !empty($product_terms) ? $product_terms : [],
                        !empty($amazon_terms) ? $amazon_terms : []
                    );
                    $unique_terms = [];
                    foreach ($all_terms as $term) {
                        if (!isset($unique_terms[$term->slug])) {
                            $unique_terms[$term->slug] = $term; // Keep only first occurrence
                        }
                    }
                    if (!empty($unique_terms) && !is_wp_error($unique_terms)) {
                        echo '<div class="filter-box" ' . $hide_category_filter . '>';
                        echo '<select class="custome-selectbox product-filter_cat filter-category" title="Filter by Category">';
                        echo '<option value="" disabled selected>Filter by Category</option>'; // Placeholder title
                        $selected = (isset($_GET['category']) && $_GET['category'] == 'All') ? 'selected' : '';
                        echo '<option value="All" ' . $selected . '>All</option>'; // "All" option
                    
                        foreach ($unique_terms as $term) {
                            $selected = (isset($_GET['category']) && $_GET['category'] === $term->name) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->name) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }

                        echo '</select></div>';
                    }
                    ?>
                    <?php
                    // $terms = get_terms(array(
                    //     'taxonomy' => 'brand',
                    //     'hide_empty' => false,
                    //     'order' => 'ASC',
                    // ));
                    $brand_terms = get_terms([
                        'taxonomy' => 'brand',
                        'hide_empty' => false,
                        'best_seller_order' => true,
                    ]);

                    $amazon_brand_terms = get_terms([
                        'taxonomy' => 'amazon_brand',
                        'hide_empty' => false,
                        'best_seller_order' => true,
                    ]);

                    // Merge both arrays and remove duplicates by slug
                    $all_brand_terms = array_merge(
                        !empty($brand_terms) ? $brand_terms : [],
                        !empty($amazon_brand_terms) ? $amazon_brand_terms : []
                    );

                    $unique_brand_terms = [];
                    foreach ($all_brand_terms as $term) {
                        if (!isset($unique_brand_terms[$term->slug])) {
                            $unique_brand_terms[$term->slug] = $term;
                        }
                    }
                    if (!empty($unique_brand_terms) && !is_wp_error($unique_brand_terms)) {
                        echo '<div class="filter-box" ' . $hide_brand_filter . '>';
                        echo '<select class="custome-selectbox product-filter_cat filter-brand" title="Filter by Brand">';
                        echo '<option value="" disabled selected>Filter by Brand</option>'; // Placeholder title
                        $selected = (isset($_GET['filter_brand']) && $_GET['filter_brand'] == 'All') ? 'selected' : '';
                        echo '<option value="All" ' . $selected . '>All</option>'; // "All" option
                    
                        foreach ($unique_brand_terms as $term) {
                            $selected = (isset($_GET['filter_brand']) && $_GET['filter_brand'] === $term->name) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->name) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }

                        echo '</select></div>';
                    }
                    ?>

                    <!-- Price Filter (Always Visible) -->
                    <?php if (have_rows('price', 'option')) {
                        $price_options = array();
                        echo '<div class="filter-box">';
                        echo '<select class="custome-selectbox product-filter_cat filter-price" title="Filter by Price">';
                        echo '<option value="" disabled selected>Filter by Prices</option>'; // Placeholder title
                        $selected = (isset($_GET['price']) && $_GET['price'] == 'All') ? 'selected' : '';
                        echo '<option value="All" ' . $selected . '>All</option>'; // "All" option
                    
                        while (have_rows('price', 'option')):
                            the_row();
                            $min = get_sub_field('min_price');
                            $max = get_sub_field('max_price');
                            if ($max > $min) {
                                $price_options[] = array('min' => $min, 'max' => $max);
                            }
                        endwhile;

                        // Sort price options by minimum price
                        usort($price_options, function ($a, $b) {
                            return $a['min'] - $b['min'];
                        });

                        foreach ($price_options as $option) {
                            $price_value = $option['min'] . '-' . $option['max'];
                            $selected = (isset($_GET['price']) && $_GET['price'] === $price_value) ? 'selected' : '';
                            echo '<option value="' . esc_attr($price_value) . '" ' . $selected . '>' .
                                get_woocommerce_currency_symbol() . esc_html($option['min']) . ' - ' .
                                get_woocommerce_currency_symbol() . esc_html($option['max']) .
                                '</option>';

                        }
                        // echo '<option value="20000">20000 +</option>';
                        // echo '<option value="20000-plus"' . (isset($_GET['price']) && $_GET['price'] === '20000-plus' ? ' selected' : '') . '>20000 +</option>';
                        // After your foreach loop
                        $selected = (isset($_GET['price']) && $_GET['price'] === '20000-plus') ? 'selected' : '';
                        echo '<option value="20000"' . $selected . '>' . get_woocommerce_currency_symbol() . '20000+</option>';



                        echo '</select></div>';
                    } ?>

                    <div class="filter-box clear_filter_desktop">
                        <input type="button" class="custom_button secondary_button clear_filter" value="Clear" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="product-listing-section listing-inner">
    <div class="container">
        <?php
        $queried_object = get_queried_object();
        $taxonomy = $queried_object->taxonomy ?? '';
        $term_slug = $queried_object->slug ?? '';

        $selected_category = isset($_GET['category']) && $_GET['category'] !== 'All' ? sanitize_title($_GET['category']) : '';
        $selected_brand = isset($_GET['filter_brand']) && $_GET['filter_brand'] !== 'All' ? sanitize_title($_GET['filter_brand']) : '';
        $selected_price = isset($_GET['price']) && $_GET['price'] !== 'All' ? explode('-', sanitize_text_field($_GET['price'])) : '';

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post_parent' => 0,
            'ignore_sticky_posts' => true,
            'tax_query' => [],
            'meta_query' => [],
        ];

        $tax_query = ['relation' => 'AND'];
        // echo $taxonomy;
        // var_dump($queried_object);
        // If it's a product category archive
        if ($taxonomy === 'product_cat') {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $term_slug,
            ];
            if (!empty($selected_brand)) {
                $tax_query[] = [
                    'taxonomy' => 'brand',
                    'field' => 'slug',
                    'terms' => $selected_brand,
                ];
            }
        }

        // If it's a brand archive
        if ($taxonomy === 'brand') {
            $tax_query[] = [
                'taxonomy' => 'brand',
                'field' => 'slug',
                'terms' => $term_slug,
            ];
            if (!empty($selected_category)) {
                $tax_query[] = [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $selected_category,
                ];
            }
        }

        // var_dump($tax_query);
        
        // Apply tax query if filters exist
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // Filter by price
        if (!empty($selected_price) && count($selected_price) === 2) {
            $args['meta_query'][] = [
                'key' => '_price',
                'value' => [(float) $selected_price[0], (float) $selected_price[1]],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }
        if (!empty($selected_price[0])) {
            $args['meta_query'][] = [
                'key' => '_price',
                'value' => [(float) $selected_price[0]],
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }

        // Execute the Query
        $loop = new WP_Query($args);


        // PAGINATION LOGIC ADDED FROM product-listing.php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $posts_per_page = 12;
        $args['posts_per_page'] = $posts_per_page;
        $args['paged'] = $paged;
        $loop = new WP_Query($args);
        if ($loop->have_posts()) {
            echo '<div class="product-list">';
            while ($loop->have_posts()) {
                $loop->the_post();
                global $product;
                $product = wc_get_product(get_the_ID());
				 if (!$product) {
                    continue;
                }

                $product_type = $product->get_type();
                $post_type = get_post_type(get_the_ID());
                if ($product_type === 'variation' || $post_type === 'product_variation' || intval($product->get_parent_id()) > 0) {
                    continue;
                }
                if ($product) {
                    // ...existing code for product box...
                    $discount = '';
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    if ($sale_price) {
                        $discount = calculate_discount($regular_price, $sale_price);
                        $discount = round($discount['discount_percentage'], 2) . '%';
                    }
                    $user_id = get_current_user_id();
                    $personalized_products = get_user_meta($user_id, '_personalized_products', true);
                    $button_text = 'Add to Registry';
                    $term_names_array = wp_get_post_terms($product->get_id(), 'brand', array('fields' => 'names'));
                    $img_src = (!empty($product->get_image_id()))
                        ? wp_get_attachment_url($product->get_image_id())
                        : get_template_directory_uri() . '/assets/images/default_image.png';
                    $is_out_of_stock = !$product->is_in_stock();
                    ?>
                    <div class="product-box <?php echo $is_out_of_stock ? 'product_disabled' : ''; ?>">
                        <div class="product-img">
                            <a href="<?php echo get_permalink($product->get_id()); ?>">
                                <img src="<?php echo esc_url($img_src); ?>" alt="products" width="288" height="212">
                            </a>
                            <?php if ($is_out_of_stock) { ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">OUT OF STOCK</span>
                                </div>
                            <?php } ?>
                            <?php if ($personalized_products && in_array($product->get_id(), $personalized_products)) { ?>
                                <div class="product-status-wrapper">
                                    <span class="product-status">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                                        ADDED
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="product-content">
                            <div class="product-title-price">
                                <h3 class="product-title">
                                    <a href="<?php echo get_permalink($product->get_id()); ?>">
                                        <?php echo esc_html($product->get_title()); ?>
                                    </a>
                                </h3>
                                <span class="product-amount">
                                    <?php echo get_woocommerce_currency_symbol() . number_format((float) $product->get_price(), 0); ?>
                                </span>
                            </div>
                            <div class="product-detail">
                                <?php if (isset($term_names_array[0])) {
                                    ?>
                                    <p class="product-detail-name"><?php echo esc_html($term_names_array[0]); ?></p>
                                    <?php
                                }
                                ?>
                                <!-- <p class="product-sku"><?php // echo esc_html($product->get_sku()); ?></p> -->
                            </div>
                            <!-- <p class="product-description"> -->
                            <?php // echo wp_trim_words($product->get_description(), 80, '...'); ?>
                            <!-- </p> -->
                            <button type="button" class="custom_button primary_button_outline add-to-personalized"
                                data-product-id="<?php echo esc_attr($product->get_id()); ?>" data-action="add">
                                <?php echo esc_html($button_text); ?>
                            </button>
                        </div>
                    </div>
                    <?php
                }
            }
            echo '</div>';
            $total_pages = $loop->max_num_pages;
            $big = 999999999;
            $pagination_args = array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $total_pages,
                'prev_next' => false,
                'prev_text' => __('<span aria-hidden="true" class="fas fa-chevron-left"></span>'),
                'next_text' => __('<span aria-hidden="true" class="fas fa-chevron-right"></span>'),
                'type' => 'array',
                'end_size' => 1,
                'mid_size' => 1
            );
            $links = paginate_links($pagination_args);
            if ($links) {
                morden_lifafa_pagination($links, $total_pages, $paged);
            }
        } else {
            echo '<div class="no-data-found">';
            echo '<strong>Oops, no results.</strong>
                <p>It seems we don’t have anything matching those filters. Try tweaking them, and we’ll show you some great options!</p>';
            echo "</div>";
        }
        wp_reset_postdata(); ?>
    </div>
</section>

<?php get_footer('shop'); ?>