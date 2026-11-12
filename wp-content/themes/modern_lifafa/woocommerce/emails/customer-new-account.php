<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 6.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);
$user = get_user_by('login', $user_login);
$first_name = $user->first_name;
$last_name = $user->last_name; ?>

<tr>
	<td style='text-align: left; padding: 50px 20px 5px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px;font-weight:400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			<strong
				style='color:#DD1265;font-weight:bold;'><?php printf(esc_html__('Dear %s %s,', 'woocommerce'), esc_html($first_name), esc_html($last_name)); ?></strong>
		</p>
	</td>
</tr>
<tr>
	<td style='text-align: left;padding: 5px 20px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px; font-weight: 400; color:#111111; margin:0; font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			Welcome to <strong style='color:#DD1265; font-weight:bold;'>Modern Lifafa</strong> – we’re so excited to be
			part of your wedding journey! 🎉</p>
	</td>
</tr>
<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			With your registry, you can:</p>
	</td>
</tr>
<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			💗 <strong style='color:#DD1265; font-weight:bold;'>Receive gifts your way</strong> – Whether it’s cash
			contributions, experiences, or must-have items, your guests can gift with ease.</p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			💗 <strong style='color:#DD1265; font-weight:bold;'>Skip the guesswork</strong> – No more duplicate gifts or
			last-minute surprises—just things you truly love.</p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			💗 <strong style='color:#DD1265; font-weight:bold;'>Make gifting seamless</strong> – Guests can send their
			blessings from anywhere in the world, and your gifts will be shipped <strong
				style='color:#DD1265; font-weight:bold;'>directly to your chosen address.</strong></p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			Start personalizing your registry today and make it uniquely yours!</p>
	</td>
</tr>

<!-- <tr>
		<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
			<p style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'><?php // printf( esc_html__( '%3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); ?></p>
		</td>
	</tr> -->

<tr>
	<td style='text-align: left;padding: 5px 20px;' align='left'>
		<p
			style='font-size: 16px; line-height: 24px; font-weight: 400; color:#111111; margin-bottom: 20px !important; margin: 0; font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			<a href='<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>'
				style='padding: 12px 22px;font-family:Segoe UI,Helvetica,Ubuntu,sans-serif;font-style: normal;font-weight: 500;font-size: 14px;line-height: 18px;color: #000000;background: #C7B65E; border-radius: 6px; border: none;box-sizing: border-box;box-shadow: none;outline: none;text-decoration: none;cursor: pointer;text-align: center;width: 210px;display: inline-block;vertical-align: middle;text-transform: uppercase;'
				target='_blank'>Sign In</a>
		</p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			Need help? We’re always here for you at info@modernlifafa.com.</p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			Wishing you love & joy,</p>
	</td>
</tr>

<tr>
	<td style='text-align: left;padding: 5px 20px 0px;' align='left'>
		<p
			style='font-size: 16px;line-height: 24px;margin-bottom: 20px !important;font-weight: 400;color:#111111;margin:0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
			The Modern Lifafa Team</p>
	</td>
</tr>

<?php /* translators: %s: Customer username */ ?>
<?php // printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_login ) ); ?>
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
<?php // printf( esc_html__( 'Thanks for creating an account on %1$s. Your username is %2$s. You can access your account area to view orders, change your password, and more at: %3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php // if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated && $set_password_url ) : ?>
<?php // If the password has not been set by the user during the sign up process, send them a link to set a new password ?>
<!-- <p><a href="<?php // echo esc_attr( $set_password_url ); ?>"><?php // printf( esc_html__( 'Click here to set your new password.', 'woocommerce' ) ); ?></a></p> -->
<?php // endif; ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
// if ($additional_content) {
// 	echo wp_kses_post(wpautop(wptexturize($additional_content)));
// }

do_action('woocommerce_email_footer', $email);
