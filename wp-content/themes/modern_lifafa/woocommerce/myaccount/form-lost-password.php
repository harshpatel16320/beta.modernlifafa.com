<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<!-- <section class="top-banner-section">
	<div class="container">
		<div class="banner-inner">
			<h1 class="banner-title">RESET PASSWORD</h1>
		</div>
	</div>
</section> -->
<section class="lrf-section">
	<div class="container">
		<div class="top-banner-section">
            <div class="banner-inner">
                <h1 class="banner-title">RESET PASSWORD</h1>
            </div>
        </div>
		<div class="lrf-inner">
			<form method="post" id="woocommerce-ResetPassword" class="input-form woocommerce-ResetPassword lost_reset_password">
				<div class="form-group">
					<input class="form-control woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" placeholder="Please provide the email linked to your account" id="user_login" autocomplete="false" aria-required="true" />
				</div>
				<!-- <p>Check your email for a reset password link.</p> -->
				<?php do_action( 'woocommerce_lostpassword_form' ); ?>
				<input type="hidden" name="wc_reset_password" value="true" />
				<div class="button-wrap">
					<button type="submit" class="custom_button secondary_button  woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>"><?php esc_html_e( 'Reset password', 'woocommerce' ); ?></button>
				</div>
				<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
			</form>
		</div>
    </div>
</section>
<script>
	 jQuery('#woocommerce-ResetPassword').validate({
        rules: {
            user_login: {
                required: true,
				email: true,
            },
        },
        messages: {
            user_login: {
				required: "Please Enter Your Email Address.",
				email: "Invalid Email Address",
			},
        },
        errorElement: "div",
        errorPlacement: function(error, element) {
            element.after(error);
        }
    });
</script>
<?php
do_action( 'woocommerce_after_lost_password_form' );
