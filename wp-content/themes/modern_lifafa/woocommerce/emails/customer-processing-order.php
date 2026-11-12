<?php
/**
 * Customer processing order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>

<?php /* translators: %s: Customer first name */ ?>
<div style="margin: 0 10px;">
<p><?php printf(esc_html__('Hi %s,', 'woocommerce'), esc_html($order->get_billing_first_name())); ?></p>

<p>
<?php
$gift_for = 0;

// Try getting from order items
foreach ($order->get_items() as $item) {
    $meta_val = $item->get_meta('gift_for');
    if (!empty($meta_val) && is_numeric($meta_val)) {
        $gift_for = (int) $meta_val;
        break;
    }
}

// Fallback to order user
if (!$gift_for) {
    $gift_for = $order->get_user_id();
}

$user_name = '';

// ✅ SAFE user fetch
if (!empty($gift_for) && ($user_info = get_userdata($gift_for))) {

    $first = get_user_meta($user_info->ID, 'first_name', true);
    $partner = get_user_meta($user_info->ID, 'partner_fname', true);

    $user_name = trim($first . ' ' . $partner);

}

// ✅ FINAL fallback
if (empty($user_name)) {
    $user_name = trim(
        $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
    );
}

// ✅ OUTPUT SAFE
printf(
    esc_html__('Thanks so much for picking a gift for %s through Modern Lifafa! 🎁', 'woocommerce'),
    esc_html($user_name)
);
?>
</p>

<p><?php esc_html_e('We’ve received your order and have notified the couple along with your gift message. They\'ll be able to ship it to themselves whenever it’s most convenient for them.', 'woocommerce'); ?>
</p>

<p><?php esc_html_e('If you have any questions, feel free to reach out to us at info@modernlifafa.com.', 'woocommerce'); ?>
</p>

<p><?php esc_html_e('Thanks again for making their celebration extra special!', 'woocommerce'); ?></p>

<p><?php esc_html_e('The Modern Lifafa Team', 'woocommerce'); ?></p>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
?>
</div>
<?php
do_action('woocommerce_email_footer', $email);
