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

global $product;
// var_dump($product);
// echo "--------------------------------------------";
// Ensure visibility.
// if (empty($product) || !$product->is_visible()) {
if (empty($product)) {
    return;
}
?>
<?php //global $product;

$discount = '';
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
if ($sale_price) {
    $discount = calculate_discount($regular_price, $sale_price);
    $discount = round($discount['discount_percentage'], 2) . '%';
}
$personalized_products = array();
$user_id = get_current_user_id();
$personalized_products = get_user_meta($user_id, '_personalized_products', true);
$button_text = 'Add to Registry';
if ($personalized_products) {
    if (in_array($product->get_id(), $personalized_products)) {
        $button_text = 'Already in Registry';
    }
}
$term_names_array = wp_get_post_terms($product->get_id(), 'brand', array('fields' => 'names'));
$product_type = $product->get_type();
$is_out_of_stock = !$product->is_in_stock();
?>
<div class="product-box <?php echo $is_out_of_stock ? 'product_disabled' : ''; ?>">
    <div class="product-img">
        <a href="<?php echo get_permalink($product->get_id()); ?>">
            <?php if (!empty($product->get_image_id()) && $product->get_image_id() != '') {
                $img_src = wp_get_attachment_url($product->get_image_id());
            } else {
                $img_src = get_template_directory_uri() . '/assets/images/default_gift.png';
            } ?>
            <img src="<?php echo $img_src; ?>" alt="products" width="288" height="212">
        </a>
        <?php if ($is_out_of_stock) { ?>
            <div class="product-status-wrapper">
                <span class="product-status">OUT OF STOCK</span>
            </div>
        <?php } ?>
        <?php if ($personalized_products) {
            if (in_array($product->get_id(), $personalized_products)) { ?>
                <div class="product-status-wrapper">
                    <span class="product-status">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/check-mark.svg" alt="check">
                        ADDED
                    </span>
                </div>
            <?php }
        } ?>
    </div>
    <div class="product-content">
        <div
            class="product-title-price <?php echo has_term('cash', 'product_cat', $product->get_id()) ? 'cash-product' : ''; ?>">
            <h3 class="product-title"><a href="<?php echo get_permalink($product->get_id()); ?>">
                    <?php echo $product->get_title(); ?>
                </a>
            </h3>
            <?php if (has_term('cash', 'product_cat', $product->get_id())) { ?>
                <p class="cash-amount-cate">Guests can gift any amount</p>
            <?php } ?>


            <?php if (!has_term('cash', 'product_cat', $product->get_id())) { ?>
                <span class="product-amout">
                    <?php echo get_woocommerce_currency_symbol() . number_format((float) $product->get_price(), 0); ?>
                </span>
            <?php } ?>
        </div>
        <div class="product-detail">
            <?php if (isset($term_names_array[0])) {
                ?>
                <p class="product-detail-name">
                    <?php echo esc_html($term_names_array[0]); ?>
                </p>
                <?php
            }
            ?>
        </div>
        <?php $product_type = $product->get_type();
        $product_permalink = get_permalink($product->get_id());
        if ($product_type === 'variable'): ?>
            <a href="<?php echo esc_url($product_permalink); ?>" class="custom_button primary_button_outline">
                Choose Options
            </a>
        <?php else: ?>
            <button type="button" class="custom_button primary_button_outline add-to-personalized <?php echo $is_out_of_stock ? 'disable_product_no_stock' : ''; ?>"
                data-product-id="<?php echo $product->get_id(); ?>"
                data-product-type="<?php echo esc_attr($product_type); ?>" data-action="add">
                <?php echo $button_text; ?>
            </button>
        <?php endif; ?>
    </div>
</div>