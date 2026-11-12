<?php /* Template Name: Product Listing */ ?>

<?php // get_header(); 
?>

<section class="our-products-section">
    <div class="container">
        <div class="filter-wrap">
            <?php if (is_user_logged_in()) { ?>
                <div class="backto-wishlist-wrapper">
                    <a href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>"
                        class="back_wishlist custom_button desktop">Back To Wishlist</a>
                    <a href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>" class="back_wishlist mobile">
                        Back To Wishlist
                    </a>
                    <div class="filter-box clear_filter_mobile">
                        <input type="button" class="custom_button secondary_button clear_filter" value="Clear Filters" />
                    </div>
                </div>
            <?php } ?>
            <div class="filter-inner">
                <?php
                //  Woo Categories (exclude "Uncategorized")
                $product_terms = get_terms([
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'order' => 'ASC',
                ]);
                $product_terms = array_filter($product_terms, function ($term) {
                    return $term->slug !== 'uncategorized';
                });
                //  Amazon Categories (exclude "Uncategorized")
                $amazon_terms = get_terms([
                    'taxonomy' => 'amazon_category',
                    'hide_empty' => true,
                    'order' => 'ASC',
                ]);
                $amazon_terms = array_filter($amazon_terms, function ($term) {
                    return $term->slug !== 'uncategorized';
                });
                echo '<div class="filter-box">';
                echo '<select class="custome-selectbox product-filter filter-category" title="Filter by Category">';
                echo '<option value="" disabled selected>Filter by Category</option>';
                $selected = (isset($_GET['category']) && $_GET['category'] == 'All') ? 'selected' : '';
                echo '<option value="All" ' . $selected . '>All</option>';
                // Combine both arrays (if they exist)
                $all_terms = array_merge(
                    !empty($product_terms) ? $product_terms : [],
                    !empty($amazon_terms) ? $amazon_terms : []
                );
                // Remove duplicates by slug
                $unique_terms = [];
                foreach ($all_terms as $term) {
                    if (!isset($unique_terms[$term->slug])) {
                        $unique_terms[$term->slug] = $term; // Keep only first occurrence
                    }
                }
                // Output the <option> list
                if (!empty($unique_terms)) {
                    foreach ($unique_terms as $term) {
                        $value = $term->slug;
                        $selected = (isset($_GET['category']) && $_GET['category'] === $value) ? 'selected' : '';
                        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                    }
                }

                echo '</select></div>';
                ?>
                <?php if (have_rows('price')) {
                    $price_options = array();
                    echo '<div class="filter-box">';
                    echo '<select class="custome-selectbox product-filter filter-price" title="Filter by Price">';
                    echo '<option value="" disabled selected>Filter by Price</option>';
                    $selected = (isset($_GET['price']) && $_GET['price'] == 'All') ? 'selected' : '';
                    echo '<option value="All" ' . $selected . '>All</option>';
                    while (have_rows('price')):
                        the_row();
                        $min = get_sub_field('min_price');
                        $max = get_sub_field('max_price');
                        if ($max > $min) {
                            $price_options[] = array('min' => $min, 'max' => $max);
                        }
                    endwhile;
                    usort($price_options, function ($a, $b) {
                        return $a['min'] - $b['min'];
                    });
                    foreach ($price_options as $option) {
                        $value = $option['min'] . '-' . $option['max'];
                        $selected = (isset($_GET['price']) && $_GET['price'] === $value) ? 'selected' : '';
                        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>';
                        echo get_woocommerce_currency_symbol() . indian_number_format($option['min']) . ' - ' . get_woocommerce_currency_symbol() . indian_number_format($option['max']);
                        echo '</option>';
                    }
                    $selected = (isset($_GET['price']) && $_GET['price'] === '20000-') ? 'selected' : '';
                    echo '<option value="20000-" ' . $selected . '>' . get_woocommerce_currency_symbol() . '20000 +</option>';
                    echo '</select></div>';
                } ?>
                <?php
                // Get terms from both taxonomies
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
                // Output dropdown
                if (!empty($unique_brand_terms)) {
                    echo '<div class="filter-box">';
                    echo '<select class="custome-selectbox product-filter filter-brand" title="Filter by Brand">';
                    echo '<option value="" disabled selected>Filter by Brand</option>';
                    $selected = (isset($_GET['filter_brand']) && $_GET['filter_brand'] == 'All') ? 'selected' : '';
                    echo '<option value="All" ' . $selected . '>All</option>';
                    foreach ($unique_brand_terms as $term) {
                        $value = esc_attr($term->slug);
                        $selected = (isset($_GET['filter_brand']) && $_GET['filter_brand'] === $term->slug) ? 'selected' : '';
                        echo '<option value="' . $value . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                    }
                    echo '</select></div>';
                } ?>
                <div class="filter-box clear_filter_desktop">
                    <input type="button" class="custom_button secondary_button clear_filter" value="Clear" />
                </div>
            </div>
        </div>
    </div>
</section>

<section class="product-listing-section">
    <div class="container">
        <?php
        // 🔹 Get Filters
        $category = isset($_GET['category']) && $_GET['category'] !== 'All' ? sanitize_text_field($_GET['category']) : '';
        $price = isset($_GET['price']) && $_GET['price'] !== 'All' ? sanitize_text_field($_GET['price']) : '';
        $brand = isset($_GET['filter_brand']) && $_GET['filter_brand'] !== 'All' ? sanitize_text_field($_GET['filter_brand']) : '';
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $current_user_id = get_current_user_id();
        // 🔹 Get admin (for amazon_product)
        $admin_users = get_users(array('role' => 'administrator'));
        $admin_ids = wp_list_pluck($admin_users, 'ID');
        // 🔹 Get all amazon products
        $amazon_query = new WP_Query(array(
            'post_type' => 'amazon_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author__in' => $admin_ids,
            'fields' => 'ids',
        ));
        $amazon_ids = $amazon_query->posts;
        // 🔹 Get Woo products
        $product_query = new WP_Query(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));
        $product_ids = $product_query->posts;
        $all_ids = array_merge($amazon_ids, $product_ids);

        // Filter products by displayed price before pagination
        if (!empty($price)) {
            $price_range = explode('-', $price, 2);

            $min_price = max(0, (float) $price_range[0]);

            $max_price = isset($price_range[1]) && $price_range[1] !== ''
                ? (float) $price_range[1]
                : null;

            $filtered_ids = [];

            foreach ($all_ids as $product_id) {
                $post_type = get_post_type($product_id);
                $product_price = null;

                if ($post_type === 'product') {
                    $wc_product = wc_get_product($product_id);

                    if (!$wc_product) {
                        continue;
                    }

                    // Variable products: use minimum displayed variation price
                    if ($wc_product->is_type('variable')) {
                        $product_price = $wc_product->get_variation_price('min', true);
                    } else {
                        $product_price = wc_get_price_to_display($wc_product);
                    }

                } elseif ($post_type === 'amazon_product') {
                    // External products use ACF price
                    $raw_price = get_post_meta($product_id, 'price', true);

                    if (is_scalar($raw_price)) {
                        $raw_price = str_replace(',', '', (string) $raw_price);
                        $raw_price = preg_replace('/[^0-9.]/', '', $raw_price);

                        if ($raw_price !== '' && is_numeric($raw_price)) {
                            $product_price = (float) $raw_price;
                        }
                    }
                }

                if ($product_price === null || !is_numeric($product_price)) {
                    continue;
                }

                $product_price = (float) $product_price;

                if (
                    $product_price >= $min_price &&
                    ($max_price === null || $product_price <= $max_price)
                ) {
                    $filtered_ids[] = $product_id;
                }
            }

            $all_ids = $filtered_ids;
        }
        $args = array(
            'post_type' => ['product', 'amazon_product'],
            'post_status' => 'publish',
            'post__in' => !empty($all_ids) ? $all_ids : [0],
            'posts_per_page' => 12,
            'paged' => $paged,
            'best_seller_order' => true,
            'post_parent' => 0,
            'ignore_sticky_posts' => true,
        );
        // ✅ Combine tax & meta queries
        $tax_query = array();
        $meta_query = array();
        // Category
        $category_slug = $category;
        if (!empty($category)) {

            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'amazon_category',
                    'field' => 'slug',
                    'terms' => strtolower($category_slug),
                ),
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => strtolower($category_slug),
                ),
            );
        }
        if (!empty($brand) && strtolower($brand) !== 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'amazon_brand',
                    'field' => 'slug',
                    'terms' => strtolower($brand),
                ),
                array(
                    'taxonomy' => 'brand',
                    'field' => 'slug',
                    'terms' => strtolower($brand),
                ),
            );
        }

        if (count($tax_query) > 1)
            $tax_query['relation'] = 'AND';
        if (count($meta_query) > 1)
            $meta_query['relation'] = 'AND';
        $args['tax_query'] = $tax_query;
        $args['meta_query'] = $meta_query;
        $products = new WP_Query($args);
        if ($products->have_posts()) {
            echo '<div class="product-list">';
            while ($products->have_posts()) {
                $products->the_post();

                $author_id = get_the_author_meta('ID');
                $author = get_user_by('ID', $author_id);
                if (get_post_type() == 'amazon_product') {
                    if (in_array('administrator', (array) $author->roles)) {
                        wc_get_template_part('content', 'amazon_product');
                    }
                } else {
                    // var_dump(get_the_ID()); 
                    wc_get_template_part('content', 'product');
                }
            }
            echo '</div>';
            $total_pages = (int) $products->max_num_pages;
            $paged = max(1, (int) $paged);
            if ($paged < $total_pages) {
                $next_url = add_query_arg(
                    array(
                        'category' => $category,
                        'price' => $price,
                        'filter_brand' => $brand,
                    ),
                    get_pagenum_link($paged + 1)
                );
                echo '<div id="infinite-scroll-trigger"
                        data-next-url="' . esc_url($next_url) . '">
                        <div class="infinite-scroll-loader" style="display:none;">
                            Loading more products...
                        </div>
                    </div>';
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-data-found">';
            echo '<strong>Oops, no results.</strong>
                    <p>It seems we don’t have anything matching those filters. Try tweaking them, and we’ll show you some great options!</p>';
            echo "</div>";
        }
        ?>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productList = document.querySelector('.product-list');
        const trigger = document.querySelector('#infinite-scroll-trigger');
        if (!productList || !trigger) return;
        const loader = trigger.querySelector('.infinite-scroll-loader');
        let loading = false;

        function preserveFilters(url) {
            const next = new URL(url, window.location.href);
            const current = new URL(window.location.href);
            ['price', 'category', 'filter_brand', 'lad'].forEach(function (key) {
                if (current.searchParams.has(key)) {
                    next.searchParams.set(key, current.searchParams.get(key));
                } else {
                    next.searchParams.delete(key);
                }
            });
            return next.toString();
        }
        let nextUrl = preserveFilters(trigger.dataset.nextUrl);
        const observer = new IntersectionObserver(async (entries) => {
            if (!entries[0].isIntersecting || loading || !nextUrl) {
                return;
            }
            loading = true;
            loader.style.display = 'block';
            try {
                const response = await fetch(nextUrl, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!response.ok) {
                    throw new Error('Failed to load products');
                }
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newProducts = doc.querySelector('.product-list');
                const newTrigger = doc.querySelector('#infinite-scroll-trigger');
                if (newProducts && newProducts.children.length) {
                    Array.from(newProducts.children).forEach(function (product) {
                        productList.appendChild(product);
                    });
                }
                if (newTrigger && newTrigger.dataset.nextUrl) {
                    nextUrl = preserveFilters(newTrigger.dataset.nextUrl);
                } else {
                    nextUrl = null;
                    observer.disconnect();
                    trigger.remove();
                }
            } catch (error) {
                console.error('Infinite scroll error:', error);
            } finally {
                loading = false;
                if (loader.isConnected) {
                    loader.style.display = 'none';
                }
            }
        }, {
            rootMargin: '500px 0px',
            threshold: 0
        });
        observer.observe(trigger);

    });
</script>
<?php // get_footer(); 
?>
