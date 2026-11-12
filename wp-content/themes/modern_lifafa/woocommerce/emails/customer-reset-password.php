<?php
/**
 * Customer Reset Password email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-reset-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 4.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

?>

<?php do_action('woocommerce_email_header', $email_heading, $email);
$user = get_user_by('login', $user_login); ?>

<?php /* translators: %s: Customer username */ ?>
<tr>
	<td style='text-align: left; padding: 50px 20px 5px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
				<?php
				$full_name = ucwords(strtolower($user->first_name . ' ' . $user->last_name));
				printf(
					esc_html__('Dear %s,', 'woocommerce'),
					sprintf(
						'<strong style="color:#DD1265; font-weight:bold;">%s</strong>',
						esc_html($full_name)
					)
				);
				?>
		</p>
	</td>
</tr>
<tr>
	<td style='text-align: left;padding: 5px 20px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px; font-weight: 400; color:#111111; margin:0; font-family: Segoe UI,Helvetica,Ubuntu,sans-serif; padding-bottom: 20px;'>
			We received a request to reset your password for your Modern Lifafa account. If you made this request, click
			the button below to reset your password:</p>
		<p
			style='font-size: 16px; line-height: 24px; font-weight: 400; color:#111111; margin: 0; font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			<a href='<?php echo esc_url(add_query_arg(array('key' => $reset_key, 'id' => $user_id), wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount')))); ?>'
				style='padding: 12px 22px;font-family:Segoe UI,Helvetica,Ubuntu,sans-serif;font-style: normal;font-weight: 500;font-size: 14px;line-height: 18px;color: #000000;background: #C7B65E; border-radius: 6px; border: none;box-sizing: border-box;box-shadow: none;outline: none;text-decoration: none;cursor: pointer;text-align: center;width: 210px;display: inline-block;vertical-align: middle;text-transform: uppercase;'
				target='_blank'>Reset Password</a>
		</p>
	</td>
</tr>
<tr>
	<td style='text-align: left; padding: 20px 20px 5px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			If you didn’t request this, you can safely ignore this email—your password will remain unchanged. For
			security reasons, this link will expire in 1 hour.</p>
	</td>
</tr>
<tr>
	<td style='text-align: left; padding: 20px 20px 5px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			If you need any help, feel free to reach out to our support team at info@modernlifafa.com.</p>
	</td>
</tr>
<tr>
	<td style='text-align: left; padding: 20px 20px 25px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			Best,</p>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;margin-botton:20px;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			The Modern Lifafa Team</p>
	</td>
</tr>
<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
