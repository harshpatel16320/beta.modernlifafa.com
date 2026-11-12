<?php
defined('ABSPATH') || exit;
get_header(); ?>

<?php
$queried_object = get_queried_object();
$is_amazon_category = ($queried_object->taxonomy ?? '') === 'amazon_category';

$taxonomy = $queried_object->taxonomy ?? '';
$hide_category_filter = ($taxonomy === 'product_cat') ? 'style="display:none;"' : '';
$hide_brand_filter = ($taxonomy === 'brand') ? 'style="display:none;"' : '';
?>
<section class="our-products-section listing-inner">
    <input type="hidden" class="qty" value="1">
    <div class="container">
        <?php $term = get_queried_object(); ?>
        <h2 class="section-title">
            <?php echo $term->name; ?>
        </h2>
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
                        'order' => 'ASC',
                    ]);

                    $amazon_terms = get_terms([
                        'taxonomy' => 'amazon_category',
                        'hide_empty' => true,
                        'order' => 'ASC',
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
                        echo '<option value="">All</option>';

                        foreach ($unique_terms as $term) {
                            $selected = (!empty($_GET['category']) && $_GET['category'] === $term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
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
                        'order' => 'ASC',
                    ]);

                    $amazon_brand_terms = get_terms([
                        'taxonomy' => 'amazon_brand',
                        'hide_empty' => false,
                        'order' => 'ASC',
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
                        echo '<option value="">All</option>';

                        foreach ($unique_brand_terms as $term) {
                            $selected = (!empty($_GET['filter_brand']) && $_GET['filter_brand'] === $term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
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
        $term = get_queried_object();
        $term_slug = $term->slug;

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        // Filters
        $selected_brand = !empty($_GET['filter_brand']) && $_GET['filter_brand'] !== 'All'
            ? sanitize_text_field($_GET['filter_brand'])
            : '';

        $selected_price = !empty($_GET['price']) && $_GET['price'] !== 'All'
            ? explode('-', sanitize_text_field($_GET['price']))
            : [];

        $args = [
            'post_type' => 'amazon_product',
            'posts_per_page' => 12,
            'paged' => $paged,
            'tax_query' => [
                [
                    'taxonomy' => 'amazon_category',
                    'field' => 'slug',
                    'terms' => $term_slug,
                ]
            ],
            'meta_query' => [],
        ];

        // Brand filter
        if ($selected_brand) {
            $args['tax_query'][] = [
                'taxonomy' => 'amazon_brand',
                'field' => 'slug',
                'terms' => $selected_brand,
            ];
        }

        // Price filter (ACF / meta)
        if (count($selected_price) === 2) {
            $args['meta_query'][] = [
                'key' => 'amazon_price',
                'value' => [(float) $selected_price[0], (float) $selected_price[1]],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }

        $query = new WP_Query($args);

        if ($query->have_posts()): ?>
            <div class="product-list">

                <?php while ($query->have_posts()):
                    $query->the_post();

                    $price = get_post_meta(get_the_ID(), 'amazon_price', true);
                    $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $image = $image ?: get_template_directory_uri() . '/assets/images/default_image.png';
                    ?>

                    <div class="product-box">
                        <div class="product-img">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>">
                            </a>
                        </div>

                        <div class="product-content">
                            <h3 class="product-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <?php if ($price): ?>
                                <span class="product-amount">₹
                                    <?php echo number_format($price); ?>
                                </span>
                            <?php endif; ?>

                            <span class="sold-via">Sold via Amazon</span>

                            <a href="<?php the_permalink(); ?>" class="custom_button primary_button_outline">
                                View Product
                            </a>
                        </div>
                    </div>

                <?php endwhile; ?>

            </div>

            <?php
            echo paginate_links([
                'total' => $query->max_num_pages,
                'current' => $paged,
            ]);
            ?>

        <?php else: ?>
            <div class="no-data-found">
                <strong>No Amazon products found.</strong>
            </div>
        <?php endif;
        wp_reset_postdata(); ?>

    </div>
</section>

<?php get_footer('shop'); ?>