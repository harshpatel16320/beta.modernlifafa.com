<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
do_action( 'woocommerce_before_customer_login_form' ); ?>
<!-- <section class="top-banner-section">
	<div class="container">
		<div class="banner-inner">
			<h1 class="banner-title">Login</h1>
		</div>
	</div>
</section> -->

<section class="lrf-section">
	<div class="container">
		<div class="top-banner-section">
			<div class="banner-inner">
				<h1 class="banner-title">Login</h1>
			</div>
		</div>
		<div class="lrf-inner">
			<form class="input-form woocommerce-form woocommerce-form-login login" id="woocommerce-form-login" method="post">
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<div class="form-group">
					<input type="text" placeholder="Username*" class="form-control woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
				</div>
				<div class="form-group">
					<div class="group-input">
						<input class="form-control woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" placeholder="Password*" id="password" autocomplete="current-password" />
						<span class="input-action-btn password_hide_show" data-id="password">
							<input type="hidden" id="show_icon" name="show_icon" value="<?php  echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
							<input type="hidden" id="hide_icon" name="hide_icon" value="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg">
							<picture>
								<img src="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg" alt="open eye" width="24" height="24">
							</picture>
						</span>
					</div>
					<div class="fr-link">
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot Password?', 'woocommerce' ); ?></a>
					</div>
				</div>

				<?php do_action( 'woocommerce_login_form' ); ?>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="custom_button secondary_button login-btn woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Login', 'woocommerce' ); ?></button>
				<div class="create-link">
					<p>Don't have an account yet?<a href="<?php echo get_permalink( get_page_by_path( 'sign-up' ) ); ?>">Sign Up</a></p>
				</div>
				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
		</div>
	</div>
</section>
<script>
	 jQuery('#woocommerce-form-login').validate({
        rules: {
            username: {
                required: true,
            },
            password: {
                required: true,
            },
        },
        messages: {
            username: "Please Enter Your Username.",
            password: "Please Enter Your Password.",
        },
        errorElement: "div",
        errorPlacement: function(error, element) {
            element.after(error);
        }
    });
</script>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
