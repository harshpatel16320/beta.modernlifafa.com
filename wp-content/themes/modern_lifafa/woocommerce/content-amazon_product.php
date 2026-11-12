<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php
// echo "hello";
$personalized_products = array();
$user_id = get_current_user_id();
$personalized_products = get_user_meta($user_id, '_personalized_amazon_products', true);
$button_text = 'Add to Registry';
// if ($personalized_products) {
//     if (in_array($product->get_id(), $personalized_products)) {
//         $button_text = 'Already in Wishlist';
//     }
// }

// Get ACF fields
$price = get_field('price', get_the_ID());
$link = get_field('product_link', get_the_ID());
// Clean and format the price
$price = trim($price);
$price = str_replace(',', '', $price);
$price = rtrim($price, '.');
$price = preg_replace('/[^\d.]/', '', $price);
$price = (float) $price;

// Check stock status
$is_out_of_stock = ($price <= 0);

// Get featured image URL
$image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_template_directory_uri() . '/assets/images/default_gift.png';
?>

<div class="product-box" data-price="<?php echo esc_attr($price); ?>">
    <div class="product-img">
        <a href="<?php echo esc_url(get_permalink()); ?>">
            <?php $img_src = '';
            if ($image_url != '' || !empty($image_url) || !$image_url) { ?>
                <img decoding="async" src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
            <?php } else {
                $img_src = get_template_directory_uri() . '/assets/images/default_image.png'; ?>
                <img decoding="async" src="<?php echo esc_url($img_src); ?>" alt="modern lifafa">
            <?php } ?>
        </a>
        <?php if ($is_out_of_stock) { ?>
            <div class="product-status-wrapper">
                <span class="product-status">OUT OF STOCK</span>
            </div>
        <?php } ?>
    </div>

    <div class="product-content">
        <div class="product-title-price">
            <h3 class="product-title">
                <a href="<?php echo esc_url(get_permalink()); ?>">
                    <?php the_title(); ?>
                </a>
            </h3>
            <?php if (!$is_out_of_stock):

                // Format number with commas
                $formatted_price = number_format($price, 0, '.', ',');
                ?>

                <p class="woocommerce-plan-cls-adding-amount">
                    <span class="woocommerce-Price-amount amount">
                        <bdi>
                            <span class="woocommerce-Price-currencySymbol">₹</span>
                            <?php echo esc_html($formatted_price); ?>
                        </bdi>
                    </span>
                </p>

            <?php else: ?>

                <!-- <p class="out-of-stock"></p> -->

            <?php endif; ?>

        </div>
        <div class="product-detail">
            <!-- <p class="product-detail-name">Amazon Product</p> -->
            <p class="product-detail-name">Sold via Amazon</p>
            <p class="product-quantity"></p>
        </div>
        <button type="button"
            class="custom_button primary_button_outline add-to-amazon-personalized <?php echo $is_out_of_stock ? 'disable_product_no_stock' : ''; ?>"
            data-product-id="<?php echo get_the_ID(); ?>" data-product-type="" data-action="add">
            <?php echo $button_text; ?>
        </button>
    </div>
</div>