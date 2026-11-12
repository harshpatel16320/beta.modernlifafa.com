<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lost-password-confirmation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined('ABSPATH') || exit; ?>
<div class="reset_wrap">
    <?php // wc_print_notice( esc_html__( 'Password reset email has been sent.', 'woocommerce' ) );
    ?>

    <?php do_action('woocommerce_before_lost_password_confirmation_message'); ?>

    <!-- <div class="woocommerce-message" role="alert"> -->
    <p><strong>Your Reset Link is on Its Way!</strong><br>
        We just sent over a link to help you reset your password.<br>
        Check your email (and maybe your spam folder—it likes to hide things).<br><br>
        If it’s taking longer than usual, feel free to try again or give us a shout at
        <a href="mailto:info@modernlifafa.com">info@modernlifafa.com</a>.
    </p>
    <!-- </div> -->

</div>
<?php do_action('woocommerce_after_lost_password_confirmation_message'); ?>