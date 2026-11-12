<?php

/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
// do_action( 'woocommerce_account_navigation' ); 

defined('ABSPATH') || exit;
$user_id = get_current_user_id();
$user = get_user_by('id', $user_id);
$user_info = get_userdata($user_id);
$profile_picture = get_user_meta($user_id, 'profile_picture', true);
// print_r($profile_picture);
$default_avatar = get_template_directory_uri() . '/assets/images/user-avtar.png';

do_action('woocommerce_before_edit_account_form'); ?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAEDEq9arQiPfIIuhsPN0mITx1tUiV2oDQ&libraries=places"></script>
<section class="form-section profile-form-section">
	<div class="container">
		<a class="back_wishlist mobile" href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>">
			<!-- <img
				class="BackwhishIcon"
				src="<?php echo get_template_directory_uri(); ?>/assets/images/back-to-top-svg.webp" alt="back-to-top"
				width="24" height="24">  -->
			Back to wishlist</a>
		<a class="back_wishlist desktop" href="<?php echo get_permalink(get_page_by_path('wishlist')); ?>">Back to
			wishlist</a>
		<form class="woocommerce-EditAccountForm edit-account" enctype="multipart/form-data" action="" method="post"
			id="woocommerce-EditAccountForm" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
			<div class="form-inner">
				<?php do_action('woocommerce_edit_account_form_start'); ?>
				<div class="img-side">
					<div class="uploaded-img">
						<?php // echo esc_attr(get_user_meta($user_id, 'profile_photo', true)); 
						?>
						<?php
						if (!empty($profile_picture)) {
        $show_image = $profile_picture; 
    } else {
        $show_image = $default_avatar;
    }

// print_r($profile_picture);

						if ($profile_picture): ?>
							<picture>
								<img src="<?php echo esc_url($show_image); ?>" class="user_profile"
									alt="<?php esc_attr_e('Profile Picture', 'woocommerce'); ?>" width="200" height="200" />
							</picture>
						<?php else: ?>
							<picture>
								<img src="<?php echo get_template_directory_uri(); ?>/assets/images/user-avtar.png"
									alt="user-image" class="user_profile" width="200" height="200">
							</picture>
						<?php endif; ?>
						<div class="upload-file">
							<input type="file" class="woocommerce-Input woocommerce-Input--file input-file"
								name="profile_picture" id="file" accept="image/*" />
							<label for="file"><span class="icon-edit"></span></label>
						</div>
					</div>

					<!-- <div class="upload-wrapper">
						<div class="upload-inner">
							<input type="file" id="file" class="profile_image" name="profile_image" accept="image/*" hidden>
							<label for="file">
								<picture>
									<img id="profile-image" class="upload_image"
										src="<?php // echo home_url(); ?>/wp-content/uploads/2025/02/upload-1.png"
										alt="upload-icon">
								</picture>
								<p>Upload Photo</p>
							</label>
						</div>
					</div> -->

					<!-- <div class="uploaded-img">
						<?php //echo esc_attr(get_user_meta($user_id, 'profile_photo', true)); 
						?>
						<picture>
							<img src="<?php //echo get_template_directory_uri(); 
							?>/assets/images/user-avtar.png" alt="user-image" width="200" height="200">
						</picture>
						<div class="upload-file">
							<input type="file" id="file">
							<label for="file"><span class="icon-edit"></span></label>
						</div>
					</div> -->
					<!-- <p><a href="<?php // echo get_permalink(get_page_by_path('products')); ?>">Add Items to your page</a> -->
					</p>
					<!-- <p><a href="<?php // echo get_permalink(get_page_by_path('received-gifts')); ?>">View received 
							gifts</a></p> -->
					<p><a href="javascript:void(0);" class="delete_account">Delete Account</a></p>
					<a href="javascript:void(0);" class="custom_button secondary_button banner-images-popup">Choose
						Banner
						Image</a>
				</div>
				<div class="signup-input-side">
					<div class="optional-details pt-0">
						<h2 class="optional-title">Personal Information <a href="javascript:void(0)"
								class="icon-edit"></a></h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<input type="text"
									class="form-control woocommerce-Input woocommerce-Input--text input-text"
									placeholder="First Name*" name="account_first_name" id="account_first_name"
									autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" />
							</div>
							<div class="form-group">
								<input type="text"
									class="form-control woocommerce-Input woocommerce-Input--text input-text"
									placeholder="Last Name" name="account_last_name" id="account_last_name"
									autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" />
							</div>
							<div class="form-group">
								<input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text"
									name="account_display_name" id="account_display_name"
									value="<?php echo esc_attr($user->display_name); ?>" />
								<input type="email"
									class="form-control woocommerce-Input woocommerce-Input--email input-text"
									name="account_email" id="account_email" placeholder="Email" autocomplete="email"
									value="<?php echo esc_attr($user->user_email); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Phone Number" class="form-control input-text"
									name="account_phone" id="account_phone"
									value="<?php echo esc_attr(get_user_meta($user_id, 'phone', true)); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Partner's First Name" class="form-control input-text"
									name="account_partner_fname" id="account_partner_fname"
									value="<?php echo esc_attr(get_user_meta($user_id, 'partner_fname', true)); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Partner's Last Name" class="form-control input-text"
									name="account_partner_lname" id="account_partner_lname"
									value="<?php echo esc_attr(get_user_meta($user_id, 'partner_lname', true)); ?>" />
							</div>
							<div class="form-group">
								<div class="group-input">
									<input type="email" class="form-control input-text" name="account_secondary_email"
										placeholder="Secondary Email" id="account_secondary_email"
										value="<?php echo esc_attr(get_user_meta($user_id, 'secondary_email', true)); ?>" />
								</div>
							</div>
						</div>
					</div>
					<div class="optional-details pt-0">
						<h2 class="optional-title">Shipping Address <a href="javascript:void(0)" class="icon-edit"></a>
						</h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<input type="hidden" id="lat" name="latitude">
								<input type="hidden" id="lng" name="longitude">
								<input type="text" name="shipping_street" id="shipping_street"
									value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_street', true)); ?>"
									placeholder="Street" class="form-control" autocomplete="off">
								<div id="suggestions" style="border: 1px solid #ccc; display: none;"></div>
							</div>
							<div class="form-group">
								<input type="text"
									value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_city', true)); ?>"
									name="shipping_city" id="shipping_city" placeholder="City" class="form-control">
							</div>
							<div class="form-group">
								<input type="text"
									value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_state', true)); ?>"
									name="shipping_state" id="shipping_state" placeholder="State" class="form-control">
							</div>
							<div class="form-group">
								<input type="text"
									value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_zipcode', true)); ?>"
									name="shipping_zipcode" id="shipping_zipcode" placeholder="Zipcode"
									class="form-control">
							</div>
							<div class="form-group">
								<input type="text"
									value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_country', true)); ?>"
									name="shipping_country" id="shipping_country" placeholder="Country"
									class="form-control">
							</div>
						</div>
					</div>
					<div class="optional-details pt-0">
						<h2 class="optional-title">Optional <a href="javascript:void(0)" class="icon-edit"></a></h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<div class="group-input">
									<input type="text" class="form-control input-text" placeholder="Wedding Venue"
										name="account_wedding_venue" id="account_wedding_venue"
										value="<?php echo esc_attr(get_user_meta($user_id, 'wedding_venue', true)); ?>" />
									<!-- <span class="input-action-btn">
										<picture>
											<img src="<?php echo get_template_directory_uri(); ?>/assets/images/info-icon.svg"
												alt="info icon" width="20" height="20">
										</picture>
										<span class="tooltip-info">You can edit this field later</span>
									</span> -->
								</div>
							</div>
							<div class="form-group">
								<div class="group-input">
									<input type="date" class="form-control input-text" name="account_wedding_date"
										placeholder="Wedding Date" id="account_wedding_date"
										value="<?php echo esc_attr(get_user_meta($user_id, 'wedding_date', true)); ?>" />
									<span class="input-action-btn">
										<!-- <picture>
											<img src="<?php //echo get_template_directory_uri(); ?>/assets/images/calendar-icon.svg" alt="calendar-icon" width="24" height="24">
										</picture> -->
									</span>
								</div>
							</div>

							<textarea name="our_story" id="our_story" rows="4" placeholder="Our Story"
								class="form-control"><?php echo esc_attr(get_user_meta($user_id, 'our_story', true)); ?></textarea>
						</div>
					</div>
					<div class="optional-details pt-0">
						<h2 class="optional-title">
							UPI Details
						</h2>

						<div class="form-group">
							<p class="upi-description">
						        Just your UPI ID, nothing else! We send cash gifts
						        straight here—no bank account or IFSC needed and guests never see it.
						    </p>
						</div>

						<?php
						/**
						 * Modern Lifafa - Couple UPI Details
						 */

						$current_user_id = get_current_user_id();

						$ml_upi_id = get_user_meta(
						    $current_user_id,
						    '_ml_upi_id',
						    true
						);
						?>

						<div class="input-form fg-three" id="ml-upi-section">

						    <!-- UPI ID -->
						    <div class="form-group">

						        <input
						            type="text"
						            id="ml_upi_id"
						            class="form-control"
						            placeholder="yourname@okhdfcbank"
						            value="<?php echo esc_attr($ml_upi_id); ?>"
						            autocomplete="off"
						        >

						    </div>

						    <!-- Confirm UPI ID -->
						    <div class="form-group">

						        <input
						            type="text"
						            id="ml_confirm_upi_id"
						            class="form-control"
						            placeholder="Confirm UPI ID"
						            autocomplete="off"
						        >

						    </div>

						</div>
						<div class=upi-submit>
							<div class="form-group">
							    <p class="upi-note">
							        Double-check this one—your cash gifts go straight here,
							        so a small typo can send them to the wrong place!
							    </p>
							</div>

						    <!-- Save UPI Button -->
						    <div class="form-group">

						        <button
						            type="button"
						            id="ml-save-upi"
						            class="custom_button secondary_button">

						            Save UPI ID

						        </button>

						    </div>

						    <!-- Message -->
						    <div class="form-group">
							    <span
							        id="ml-upi-message"
							        class="ml-upi-message"
							        style="display:none;">
							    </span>
							</div>
						</div>
					</div>

					<?php
					/**
					 * Hook where additional fields should be rendered.
					 *
					 * @since 8.7.0
					 */
					// do_action('woocommerce_edit_account_form_fields');
					?>

					<?php
					/**
					 * My Account edit account form.
					 *
					 * @since 2.6.0
					 */
					do_action('woocommerce_edit_account_form');
					?>
					<div class="button-wrap">
						<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
						<button type="submit"
							class="custom_button secondary_button woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
							name="save_account_details"
							value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><?php esc_html_e('Save & View Profile', 'woocommerce'); ?></button>
						<input type="hidden" name="action" value="save_account_details" />
					</div>
					<?php do_action('woocommerce_edit_account_form_end'); ?>
				</div>
			</div>
		</form>
	</div>
</section>
<?php
function display_user_banner()
{
	if (!is_user_logged_in())
		return;

	$selected_banner = get_user_meta(get_current_user_id(), 'selected_banner', true);
	if ($selected_banner) {
		echo '<h3>Your Selected Banner</h3>';
		echo '<img src="' . esc_url($selected_banner) . '" style="max-width: 100%; height: auto;">';
	}
}
add_action('woocommerce_account_dashboard', 'display_user_banner', 15);

// Display banne image function
//echo display_user_banner();
?>

<script>
	jQuery.validator.addMethod("customname", function (value, element) {
		return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
	}, "Please enter a valid name");
	jQuery.validator.addMethod("customphone", function (value, element) {
		return this.optional(element) || /^\+?\d{10,14}$/.test(value);
	}, "Please enter a valid phone number");
	jQuery.validator.addMethod("imageType", function (value, element) {
		return this.optional(element) || /\.(jpe?g|png|webp)$/i.test(value);
	}, "Please upload a valid image (JPEG, JPG, PNG, WEBP).");
	jQuery('#woocommerce-EditAccountForm').validate({
		rules: {
			profile_picture: {
				imageType: true
			},
			account_first_name: {
				required: true,
				customname: true,
				minlength: 2
			},
			account_last_name: {
				required: true,
				customname: true,
				minlength: 2
			},
			account_email: {
				required: true,
				email: true,
			},
			account_phone: {
				// required: true,
				customphone: true,
			},
			account_partner_fname: {
				required: true,
				customname: true,
				minlength: 2
			},
			account_partner_lname: {
				required: true,
				customname: true,
				minlength: 2
			}
		},
		messages: {
			profile_picture: {
				imageType: "Please upload a valid image (JPEG, PNG, WEBP)."
			},
			account_first_name: {
				required: "Please Enter Your First Name.",
				customphone: "Please Enter valid Name",
				minlength: "Please Enter valid Name",
			},
			account_last_name: {
				required: "Please Enter Your Last Name.",
				customphone: "Please Enter Valid Name",
				minlength: "Please Enter valid Name",
			},
			account_email: {
				required: "Please Enter Your Email.",
				email: "Please Enter Valid Email",
			},
			account_phone: {
				// required: "Please Enter Your Phone.",
				customphone: "Please Enter Valid Phone Number",
			},
			account_partner_fname: {
				required: "Please Enter Your Partner's First Name.",
				customphone: "Please Enter valid Name",
				minlength: "Please Enter valid Name",
			},
			account_partner_lname: {
				required: "Please Enter Your Partner's Last Name.",
				customphone: "Please Enter valid Name",
				minlength: "Please Enter valid Name",
			}
		},
		errorElement: "div",
		errorPlacement: function (error, element) {
			element.after(error);
		}
	});
</script>
<!-- Upi id -->
<script>
jQuery(document).ready(function ($) {

    $('#ml-save-upi').on('click', function () {

        var upiId = $('#ml_upi_id')
            .val()
            .trim()
            .toLowerCase();

        var confirmUpiId = $('#ml_confirm_upi_id')
            .val()
            .trim()
            .toLowerCase();

        var message = $('#ml-upi-message');

        var button = $(this);

        message.hide().text('');

        // Required validation
        if (!upiId || !confirmUpiId) {

            message
                .text('Please enter and confirm your UPI ID.')
                .show();

            return;
        }

        // Match validation
        if (upiId !== confirmUpiId) {

            message
            	.removeClass('success')
        		.addClass('error')
                .text('UPI IDs do not match.')
                .show();

            return;
        }

        // Disable button while saving
        button
            .prop('disabled', true)
            .text('Saving...');

        $.ajax({

            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',

            type: 'POST',

            data: {

                action: 'ml_save_upi_id',

                nonce: '<?php echo esc_js(wp_create_nonce('ml_upi_nonce')); ?>',

                upi_id: upiId,

                confirm_upi_id: confirmUpiId

            },

            success: function (response) {

			    if (response.success) {

			        // Show success message in green
			        message
			            .removeClass('error')
			            .addClass('success')
			            .text(response.data.message)
			            .show();

			        // Update saved UPI ID field
			        $('#ml_upi_id').val(response.data.upi_id);

			        // Clear confirmation field
			        $('#ml_confirm_upi_id').val('');

			    } else {

			        // Show server validation error in red
			        message
			            .removeClass('success')
			            .addClass('error')
			            .text(
			                response.data.message ||
			                'Unable to save UPI ID.'
			            )
			            .show();

			    }

			},

            error: function () {

                message
                    .text('Something went wrong. Please try again.')
                    .show();

            },

            complete: function () {

                button
                    .prop('disabled', false)
                    .text('Save UPI ID');

            }

        });

    });

});
</script>
