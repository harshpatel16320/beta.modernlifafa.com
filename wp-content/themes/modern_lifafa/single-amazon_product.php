<?php
if (!defined('ABSPATH'))
    exit;
get_header('shop');
?>
<?php
// Get the current post/product
global $product;
$gift_for = isset($_GET['gift_for']) ? intval($_GET['gift_for']) : 0;
$gift_for_user_id = isset($_GET['gift_for']) ? intval($_GET['gift_for']) : 0;
$user_exists = $gift_for_user_id ? get_user_by('ID', $gift_for_user_id) : false;
$logged_in = is_user_logged_in();
$user_id = $logged_in ? get_current_user_id() : '';
$disable_class = ($gift_for && !$logged_in) ? ['disabled'] : ['qty'];
if (function_exists('wc_print_notices')) {
    wc_print_notices();
}
?>
<section class="our-products-section">
    <div class="container">
        <?php if ($logged_in): ?>
            <a href="<?php echo home_url('/wishlist'); ?>" class="personalised-page" title="Go to Personalised Page">Go to
                Wishlist</a>
        <?php endif; ?>
    </div>
</section>
<?php while (have_posts()):
    the_post();

    $product_link = get_field('product_link', get_the_ID());
    // First use saved platform name.
    $platform_name = get_post_meta(get_the_ID(), 'external_platform', true);
    // If not saved, detect from URL.
    if (empty($platform_name)) {
        if (function_exists('mlf_external_platform_name')) {
            $platform_name = mlf_external_platform_name($product_link);
        } else {
            $host = strtolower((string) wp_parse_url($product_link, PHP_URL_HOST));
            $host = preg_replace('/^www**\\.**/', '', $host);
            $platforms = [
                'amazon.in'          => 'Amazon',
                'amazon.com'         => 'Amazon',
                'flipkart.com'       => 'Flipkart',
                'meesho.com'         => 'Meesho',
                'tataneu.com'        => 'Tata Neu',
                'snapdeal.com'       => 'Snapdeal',
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
                'zeptonow\.com'       => 'Zepto',
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
                    substr($host, - (strlen($domain) + 1)) === '.' . $domain
                ) {
                    $platform_name = $name;
                    break;
                }
            }
        }
    }
    // Normalize old Amazon naming.
    if (
        strtolower($platform_name) === 'amazon india' ||
        strtolower($platform_name) === 'amazon.in'
    ) {
        $platform_name = 'Amazon';
    }
    $is_amazon = (strtolower($platform_name) === 'amazon');
?>
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail-wrap">
                <!-- Product Image -->
                <div class="product-img">
                    <div class="all-rated-img-cls-adding">
                        <picture class="product-image-cls-adding-all">
                            <?php
                            $product_id = get_the_ID();
                            $image_url = mlf_get_registry_product_image(
                                $product_id,
                                'large'
                            );
                            ?>
                            <img src="<?php echo esc_url($image_url); ?>"
                                class="featured-image product-feature-img-cls-adding"
                                alt="<?php echo esc_attr(get_the_title($product_id)); ?>">
                        </picture>
                    </div>
                </div>
                <!-- Product Details -->
                <div class="product-details">
                    <h3 class="product-title"><?php the_title(); ?></h3>
                    <!-- Product Price -->
                    <?php
                    $price = get_field('price');
                    $is_out_of_stock = ($price <= 0);
                    if ($price) {
                        // Clean and convert the price to a proper numeric value
                        $price = str_replace(',', '', $price); // remove commas
                        $price = preg_replace('/[^\d.]/', '', $price); // remove any non-numeric characters except dot
                        $price = (float) $price; // convert to float
                        // Check stock status
                        $is_out_of_stock = ($price <= 0);
                        echo '<div class="price">₹' . number_format($price, 0, '.', ',') . '</div>';
                    }
                    ?>
                    <?php
                    $last_refresh = get_the_modified_time('F j, Y, g:i a'); // Example: October 17, 2025, 2:30 pm
                    ?>
                    <p class="spacing-cum-cls-added">
                        <?php echo esc_html($platform_name); ?> Price as of
                        <span class="last-refresh">
                            <?php echo esc_html($last_refresh); ?>
                        </span>
                    </p>
                    <p class="spacing-cum-cls-added">Price and availability are subject to change</p>
                    <hr class="line">
                    <!-- Product Description Tabs -->
                    <div class="product-content">
                        <div class="tab-listing-cls-all-tabbing">
                            <div class="product-tabs">
                                <ul class="tab-nav">
                                    <li class="tab-link active" data-tab="product-description">Product Description</li>
                                </ul>
                                <div id="product-description"
                                    class="tab-content active newproduct-description-cls-adding-common product-description-content">
                                    <?php the_content(); ?>
                                </div>
                                <button class="see-more-btn" style="display:none;">See More</button>
                                <div class="spacing-additional-top-cls-here">
                                    <p class="spacing-cum-cls-added new-color-cls-adding-here">
                                        This product is sold via
                                        <?php echo esc_html($platform_name); ?>.
                                        <?php if ($is_amazon): ?>
                                            <br>
                                            As an Amazon Associate, Modern Lifafa earns from qualifying purchases.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add to Wishlist Button -->
                    <div class="add-product cash-product-gift new-add-product-gift-card-cls">
                        <?php $amazon_url = $product_link; ?>
                        <?php
                        $purchased = false;
                        $original_user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : 0;
                        if (is_user_logged_in() && !$purchased && (!$original_user_id)): ?>
                            <!-- <button class="custom_button add-to-personalized" type="button">Add to Wishlist</button> -->
                            <button type="button"
                                class="custom_button primary_button_outline add-to-amazon-personalized <?php echo $is_out_of_stock ? 'disable_product_no_stock' : ''; ?>"
                                data-product-id="<?php echo get_the_ID(); ?>" data-amazon-url="<?php echo $amazon_url; ?>"
                                data-product-type="" data-action="add">
                                ADD TO REGISTRY</button>
                        <?php endif; ?>
                        <?php if ($amazon_url): ?>
                            <a data-details-amazon-url="<?php echo esc_url($amazon_url); ?>" target="_blank"
                                rel="nofollow sponsored noopener" class="custom_button details-buy-amazon">
                                VIEW ON <?php echo esc_html(strtoupper($platform_name)); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endwhile; ?>
<?php get_footer('shop'); ?>