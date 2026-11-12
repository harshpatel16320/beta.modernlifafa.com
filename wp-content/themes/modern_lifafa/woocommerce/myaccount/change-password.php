<div class="change-password-section contact-form-section">
    <div class="container">
        <div class="top-banner-section">
            <div class="banner-inner">
                <h1 class="banner-title">Change Password</h1>
            </div>
        </div>
        <div class="change-password-inner">
            <?php wc_print_notices(); ?>
            <form method="post" class="common-form-wrap woocommerce-EditAccountForm edit-account"
                id="woocommerce-edit-account" action="">
                <?php do_action('woocommerce_edit_account_form_start'); ?>
                <div class="input-form fg-three">
                    <div class="form-group">
                        <div class="group-input">
                            <input type="password" placeholder="Old Password"
                                class="form-control woocommerce-Input woocommerce-Input--text input-text"
                                name="password_current" id="password_current" autocomplete="off" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="group-input">
                            <input type="password" placeholder="New Password"
                                class="form-control woocommerce-Input woocommerce-Input--text input-text"
                                name="password_1" id="password_1" autocomplete="off" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="group-input">
                            <input type="password" placeholder="Confirm Password"
                                class="form-control woocommerce-Input woocommerce-Input--text input-text"
                                name="password_2" id="password_2" autocomplete="off" />
                            <!-- <span class="input-action-btn password_hide_show" data-id="password_2">
                                <input type="hidden" id="show_icon" name="show_icon" value="<?php // echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
                                <input type="hidden" id="hide_icon" name="hide_icon" value="<?php // echo get_template_directory_uri(); ?>/assets/images/icon-password.svg">
                                <picture>
                                    <img src="<?php // echo get_template_directory_uri(); ?>/assets/images/icon-password.svg" alt="open eye" width="24" height="24">
                                </picture>
                            </span> -->
                        </div>
                    </div>
                </div>
                <?php do_action('woocommerce_edit_account_form'); ?>
                <div class="button-wrap">
                    <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
                    <button type="submit" class="custom_button secondary_button" name="save_account_details"
                        value="<?php esc_attr_e('Update', 'woocommerce'); ?>"><?php esc_html_e('Update', 'woocommerce'); ?></button>
                    <input type="hidden" name="action" value="save_account_details" />
                </div>
                <?php do_action('woocommerce_edit_account_form_end'); ?>
            </form>
        </div>
    </div>
</div>

<script>
    jQuery.validator.addMethod("checklower", function (value) {
        return /[a-z]/.test(value);
    });
    jQuery.validator.addMethod("checkupper", function (value) {
        return /[A-Z]/.test(value);
    });
    jQuery.validator.addMethod("checkdigit", function (value) {
        return /[0-9]/.test(value);
    });
    jQuery.validator.addMethod("notEqualToCurrent", function (value, element) {
        return value !== $("#password_current").val();
    }, "New password must be different from the current password.");
    jQuery.validator.addMethod("strongPassword", function (value, element) {
        return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/.test(value);
    }, "Password must be at least 8 characters long and contain at least one number, one lowercase and one uppercase letter.");
    jQuery('#woocommerce-edit-account').validate({
        rules: {
            password_current: {
                required: true,
            },
            password_1: {
                required: true,
                strongPassword: true,
                notEqualToCurrent: true
            },
            password_2: {
                equalTo: "#password_1",
            },
        },
        messages: {
            password_current: "Please Enter Your Username.",
            password_1: {
                required: "Please Enter Your Password.",
                strongPassword: "Must be 8+ chars with a number, lowercase and uppercase.",
                notEqualToCurrent: "New password must be different from the current password.",
            },
            password_2: {
                equalTo: "New password and confirm Password are not same.",
            }
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
            element.after(error);
        }
    });
</script>