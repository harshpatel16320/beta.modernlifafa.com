<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $amazonproduct;
//var_dump($product);

// Ensure visibility.
if (empty($amazonproduct) || !$amazonproduct->is_visible()) {
    return;
}

// while ($query->have_posts()) {
// $query->the_post();
$price = get_field('price', get_the_ID());
$link = get_field('product_link', get_the_ID());
?>
<div class="box presonalised-item" data-price="<?php echo esc_html($price); ?>">
    <div class="product-img hello123">
        <a href="<?php echo esc_url($link); ?>">
            <img decoding="async" src="<?php $image_url = get_the_post_thumbnail_url(get_the_Id(), 'full'); ?>"
                alt="product">
        </a>
        <button class="custom_button remove_product" data-product_id="<?php echo get_the_Id(); ?>">
            <i aria-hidden="true" class="fas fa-trash"></i>
        </button>
    </div>

    <div class="product-info">
        <div class="product-name">
            <h3 class="product-title">
                <a href="<?php echo esc_url($link); ?>"><?php the_title(); ?></a>
            </h3>
            <p><span class="woocommerce-Price-amount amount"><bdi><span
                            class="woocommerce-Price-currencySymbol">₹</span>1,400</bdi></span></p>
        </div>
        <div class="product-detail">
            <p class="product-detail-name">Amazon Product</p>
            <p class="product-quantity"></p>
        </div>
    </div>
</div>
<?php
// }