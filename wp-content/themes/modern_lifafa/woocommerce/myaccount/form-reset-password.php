<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-reset-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>
<div class="change-password-section contact-form-section lrf-section">
    <div class="container">
        <div class="top-banner-section">
            <div class="banner-inner">
                <h1 class="banner-title">RESET PASSWORD</h1>
            </div>
        </div>
        <div class="change-password-inner lrf-inner">
			<form method="post" id="woocommerce-ResetPassword" class="common-form-wrap woocommerce-ResetPassword lost_reset_password">
				<div class="input-form fg-three">
					<div class="form-group">
                        <div class="group-input">
                            <input type="password" placeholder="New Password" class="form-control woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" />
                            <span class="input-action-btn password_hide_show" data-id="password_1">
                                <input type="hidden" id="show_icon" name="show_icon" value="<?php echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
                                <input type="hidden" id="hide_icon" name="hide_icon" value="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg" alt="open eye" width="24" height="24">
                                </picture>
                            </span>
                        </div>
                    </div>
					<div class="form-group">
                        <div class="group-input">
                            <input type="password" placeholder="Confirm Password" class="form-control woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" />
                            <span class="input-action-btn password_hide_show" data-id="password_2">
                                <input type="hidden" id="show_icon" name="show_icon" value="<?php echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
                                <input type="hidden" id="hide_icon" name="hide_icon" value="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg" alt="open eye" width="24" height="24">
                                </picture>
                            </span>
                        </div>
                    </div>
					<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
					<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

					<?php do_action( 'woocommerce_resetpassword_form' ); ?>

					<div class="button-wrap pt-0">
						<input type="hidden" name="wc_reset_password" value="true" />
						<button type="submit" class="custom_button secondary_button primary_button_outline woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>"><?php esc_html_e( 'Save', 'woocommerce' ); ?></button>
					</div>

					<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
    // jQuery.validator.addMethod("strongPassword", function(value, element) {
    //     return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/.test(value);
    // }, "Must be 8+ chars with a number, lowercase and uppercase.");
	 jQuery('#woocommerce-ResetPassword').validate({
        rules: {
            // password_1: {
            //     required: true,
            //     strongPassword : true,
            // },
            password_2: {
                equalTo: "#password_1",
            },
        },
        messages: {
            password_current: "Please Enter Your Username.",
            // password_1: {
            //     required:  "Please Enter Your Password.",
            //     strongPassword: "Must be 8+ chars with a number, lowercase and uppercase.",
            //     notEqualToCurrent: "New password must be different from the current password.",
            // },
            password_2: {
                equalTo: "New password and confirm Password are not same.",
            }
        },
        errorElement: "div",
        errorPlacement: function(error, element) {
            element.after(error);
        }
    });
</script>
<?php
do_action( 'woocommerce_after_reset_password_form' );

