<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

 // phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();

// We make sure the order belongs to the user. This will also be true if the user is a guest, and the order belongs to a guest (userID === 0).
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php do_action( 'woocommerce_order_details_before_order_table_items', $order ); ?>

			<?php foreach ( $order_items as $item_id => $item ) : ?>
				<?php $product = $item->get_product(); ?>

				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
					<td class="woocommerce-table__product-name product-name">
						<?php
						echo esc_html( $item->get_name() ) . '&nbsp;';
						echo ' <strong class="product-quantity">×&nbsp;' . esc_html( $item->get_quantity() ) . '</strong>';

						// Filter meta data and remove gift_for and _vendor_id
						$meta_data = $item->get_formatted_meta_data( '' );
						if ( ! empty( $meta_data ) ) {
							echo '<dl class="variation">';
							foreach ( $meta_data as $meta ) {
								if ( in_array( $meta->key, [ 'gift_for', '_vendor_id', '_reduced_stock' ] ) ) {
									continue;
								}								
								echo '<dt>' . esc_html( wc_attribute_label( $meta->key ) ) . ':</dt><dd>' . wp_kses_post( $meta->value ) . '</dd>';
							}
							echo '</dl>';
						}

						// 🎁 Gift For (user ID -> name)
						$gift_for_id = $item->get_meta( 'gift_for' );
						if ( $gift_for_id ) {
							$first = get_user_meta( $gift_for_id, 'first_name', true );
							$last  = get_user_meta( $gift_for_id, 'partner_fname', true );
							if ( $first || $last ) {
								echo '<p class="gift-for"><strong>Gift for:</strong> ' . esc_html($first.' & '.$last ) . '</p>';
							}
						}

						$gift_message = $order->get_meta('gift_message');
						if (!empty($gift_message)) {
							echo '<p><strong>' . __('Gift Message') . ':</strong><br>' . nl2br(esc_html($gift_message)) . '</p>';
						}

						// 📦 Deliver to Couple Address (custom block field)
						$deliver_to_couple = $order->get_meta('deliver_to_couple');
						$display_value = ($deliver_to_couple === 'yes') ? 'Yes' : 'No';

						echo '<p><strong>' . __('Ship To Couple') . ':</strong> ' . $display_value . '</p>';
						?>
					</td>

					<td class="woocommerce-table__product-total product-total">
						<?php echo $order->get_formatted_line_subtotal( $item ); ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_order_details_after_order_table_items', $order ); ?>
		</tbody>


		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses( nl2br( wptexturize( $order->get_customer_note() ) ), array() ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
