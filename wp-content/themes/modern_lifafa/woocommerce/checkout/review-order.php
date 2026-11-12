<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
			<th class="product-total"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action('woocommerce_review_order_before_cart_contents');

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

			if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
				?>
				<tr
					class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
					<td class="product-name">
						<?php
						echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;';
						echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key);

						// Gift for name from user ID (if available)
						if (isset($cart_item['gift_for']) && !empty($cart_item['gift_for'])) {
							$user_id = $cart_item['gift_for'];
							$first_name = get_user_meta($user_id, 'first_name', true);
							$last_name = get_user_meta($user_id, 'partner_fname', true);
							if ($first_name || $last_name) {
								echo '<p class="gift-for"><strong>Gift for:</strong> ' . esc_html($first_name . ' & ' . $last_name) . '</p>';
							}
						}

						// Show product variation details etc.
						// Manually render cart item meta except 'gift_for' and '_vendor_id'
						// if ( isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
						// 	foreach ( $cart_item['variation'] as $key => $value ) {
						// 		if ( ! in_array( $key, ['gift_for', '_vendor_id'] ) ) {
						// 			$label = wc_attribute_label( $key );
						// 			$display = is_array( $value ) ? implode( ', ', $value ) : $value;
						// 			echo '<dl class="variation"><dt>' . esc_html( $label ) . ':</dt><dd>' . esc_html( $display ) . '</dd></dl>';
						// 		}
						// 	}
						// }
				
						if (isset($cart_item['variation']) && is_array($cart_item['variation'])) {
							foreach ($cart_item['variation'] as $key => $value) {
								if (!in_array($key, ['gift_for', '_vendor_id'])) {
									$attribute_name = str_replace('attribute_', '', $key);
									$label = wc_attribute_label($attribute_name);
									$label = ucwords($label);
									$display = is_array($value) ? implode(', ', $value) : $value;
									echo '<dl class="variation"><dt>' . esc_html($label) . ':</dt><dd>' . esc_html($display) . '</dd></dl>';

								}
							}
						}

						if (isset($cart_item['custom_data']) && is_array($cart_item['custom_data'])) {
							foreach ($cart_item['custom_data'] as $key => $value) {
								if (!in_array($key, ['gift_for', '_vendor_id'])) {
									echo '<dl class="variation"><dt>' . esc_html($key) . ':</dt><dd>' . esc_html($value) . '</dd></dl>';
								}
							}
						}

						?>
					</td>

					<td class="product-total">
						<?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action('woocommerce_review_order_after_cart_contents');
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
			<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
				<th><?php wc_cart_totals_coupon_label($coupon); ?></th>
				<td><?php wc_cart_totals_coupon_html($coupon); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()): ?>

			<?php do_action('woocommerce_review_order_before_shipping'); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action('woocommerce_review_order_after_shipping'); ?>

		<?php endif; ?>

		<?php foreach (WC()->cart->get_fees() as $fee): ?>
			<tr class="fee">
				<th><?php echo esc_html($fee->name); ?></th>
				<td><?php wc_cart_totals_fee_html($fee); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')): ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax): // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
						<th><?php echo esc_html($tax->label); ?></th>
						<td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr class="tax-total">
					<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_review_order_before_order_total'); ?>

		<tr class="order-total">
			<th><?php esc_html_e('Total', 'woocommerce'); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action('woocommerce_review_order_after_order_total'); ?>

	</tfoot>
</table>