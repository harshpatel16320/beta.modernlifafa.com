<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined( 'ABSPATH' ) || exit;
$user_id = get_current_user_id(); 
$user_info = get_userdata($user_id); 
$profile_picture = get_user_meta( $user_id, 'profile_picture', true );
//var_dump($profile_picture);
do_action( 'woocommerce_before_edit_account_form' ); ?>
<section class="form-section profile-form-section">
	<div class="container">
		<form class="woocommerce-EditAccountForm edit-account" action="" enctype="multipart/form-data" method="post" id="woocommerce-EditAccountForm" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
			<div class="form-inner">
				<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
				<div class="img-side">
					<div class="uploaded-img">
						<?php echo esc_attr(get_user_meta($user_id, 'profile_photo', true)); ?>
						<?php if ( $profile_picture ) : ?>
							<picture>
								<img src="<?php echo esc_url( $profile_picture ); ?>" id="profile-image" class="user_profile" alt="<?php esc_attr_e( 'Profile Picture', 'woocommerce' ); ?>" width="200" height="200" />
							</picture>
						<?php else: ?>
							<picture>
								<img src="<?php  echo get_template_directory_uri(); ?>/assets/images/user-avtar.png" class="user_profile" id="profile-image" alt="user-image" width="200" height="200">
							</picture>
						<?php endif; ?>
						<div class="upload-file">
							<input type="file" class="woocommerce-Input change_profile_picture woocommerce-Input--file input-file" name="profile_picture" id="file" accept="image/*" />
							<label for="file"><span class="icon-edit"></span></label>
						</div>
					</div>
					<span id="file-name" class="file-name"></span>
					<!-- <div class="uploaded-img">
						<?php //echo esc_attr(get_user_meta($user_id, 'profile_photo', true)); ?>
						<picture>
							<img src="<?php //echo get_template_directory_uri(); ?>/assets/images/user-avtar.png" alt="user-image" width="200" height="200">
						</picture>
						<div class="upload-file">
							<input type="file" id="file">
							<label for="file"><span class="icon-edit"></span></label>
						</div>
					</div> -->
					<p><a href="<?php echo get_permalink(get_page_by_path('products')); ?>">Add Items to your page</a></p>
					<p><a href="<?php echo get_permalink(get_page_by_path('received-gifts')); ?>">View received gifts</a></p>
				</div>
				<div class="signup-input-side">
					<div class="optional-details pt-0">
						<h2 class="optional-title">Personal Information <a href="javascript:void(0)" class="icon-edit"></a></h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<input type="text" class="form-control woocommerce-Input woocommerce-Input--text input-text" placeholder="First Name*" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
							</div>
							<div class="form-group">
								<input type="text" class="form-control woocommerce-Input woocommerce-Input--text input-text"  placeholder="Last Name" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
							</div>
							<div class="form-group">
								<input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
								<input type="email" class="form-control woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" placeholder="Email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Phone Number" class="form-control input-text" name="account_phone" id="account_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'phone', true)); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Partner's First Name" class="form-control input-text" name="account_partner_fname" id="account_partner_fname" value="<?php echo esc_attr(get_user_meta($user_id, 'partner_fname', true)); ?>" />
							</div>
							<div class="form-group">
								<input type="text" placeholder="Partner's Last Name" class="form-control input-text" name="account_partner_lname" id="account_partner_lname" value="<?php echo esc_attr(get_user_meta($user_id, 'partner_lname', true)); ?>" />
							</div>
							<div class="form-group">
								<div class="group-input">
									<input type="email" class="form-control input-text" name="account_secondary_email" placeholder="Secondary Email"  id="account_secondary_email" value="<?php echo esc_attr(get_user_meta($user_id, 'secondary_email', true)); ?>" />
								</div>
							</div>
						</div>
					</div>
					<div class="optional-details pt-0">
						<h2 class="optional-title">Shipping Address <a href="javascript:void(0)" class="icon-edit"></a></h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<input type="text" name="shipping_street" id="shipping_street" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_street', true)); ?>" placeholder="Street" class="form-control">
							</div>
							<div class="form-group">
								<input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_city', true)); ?>" name="shipping_city" id="shipping_city" placeholder="City" class="form-control">
							</div>
							<div class="form-group">
								<input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_state', true)); ?>" name="shipping_state" id="shipping_state" placeholder="State" class="form-control">
							</div>
							<div class="form-group">
								<input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_zipcode', true)); ?>" name="shipping_zipcode" id="shipping_zipcode" placeholder="Zipcode" class="form-control">
							</div>
							<div class="form-group">
								<input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_country', true)); ?>" name="shipping_country" id="shipping_country" placeholder="Country" class="form-control">
							</div>
						</div>
					</div>
					<div class="optional-details pt-0">
                        <h2 class="optional-title">Optional <a href="javascript:void(0)" class="icon-edit"></a></h2>
                        <div class="input-form fg-three">
							<div class="form-group">
								<div class="group-input">
									<input type="text" class="form-control input-text" placeholder="Wedding Venue" name="account_wedding_venue" id="account_wedding_venue" value="<?php echo esc_attr(get_user_meta($user_id, 'wedding_venue', true)); ?>" />
									<span class="input-action-btn">
										<picture>
											<img src="<?php echo get_template_directory_uri(); ?>/assets/images/info-icon.svg" alt="info icon" width="20" height="20">
										</picture>
										<span class="tooltip-info">You can edit this field later</span>
									</span>
								</div>
							</div>
							<div class="form-group">
								<div class="group-input">
									<input type="date" class="form-control input-text" name="account_wedding_date" placeholder="Wedding Date" id="account_wedding_date" value="<?php echo esc_attr(get_user_meta($user_id, 'wedding_date', true)); ?>" />
									<span class="input-action-btn">
										<picture>
											<img src="<?php echo get_template_directory_uri(); ?>/assets/images/calendar-icon.svg" alt="calendar-icon" width="24" height="24">
										</picture>
									</span>
								</div>
							</div>
							<div class="form-group">
								<textarea name="our_story" id="our_story" rows="4" placeholder="Our Story" class="form-control"><?php echo esc_attr(get_user_meta($user_id, 'our_story', true)); ?></textarea>
							</div>
						</div>
					</div>
					<div class="optional-details pt-0">
						<h2 class="optional-title">Bank Details <a href="javascript:void(0)" class="icon-edit"></a></h2>
						<div class="input-form fg-three">
							<div class="form-group">
								<input type="text" name="account_number" value="<?php echo esc_attr(get_user_meta($user_id, 'account_number', true)); ?>" placeholder="Account number" class="form-control">
							</div>

							<div class="form-group">
								<input type="text" name="bank_name" value="<?php echo esc_attr(get_user_meta($user_id, 'bank_name', true)); ?>" placeholder="Bank Name" class="form-control">
							</div>

							<div class="form-group">
								<input type="text" name="bank_ifsc" value="<?php echo esc_attr(get_user_meta($user_id, 'bank_ifsc', true)); ?>" placeholder="IFSEC" class="form-control">
							</div>

							<div class="form-group">
								<input type="text" name="bank_city" value="<?php echo esc_attr(get_user_meta($user_id, 'bank_city', true)); ?>" placeholder="City" class="form-control">
							</div>
						</div>
					</div>
					<?php
						/**
						 * Hook where additional fields should be rendered.
						 *
						 * @since 8.7.0
						 */
						do_action( 'woocommerce_edit_account_form_fields' );
					?>

					<?php
						/**
						 * My Account edit account form.
						 *
						 * @since 2.6.0
						 */
						do_action( 'woocommerce_edit_account_form' );
					?>
					<div class="button-wrap">
						<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
						<button type="submit" class="custom_button primary_button_outline woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="save_account_details" value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><span class="icon-small-right-angle"></span><?php esc_html_e('Update', 'woocommerce'); ?></button>
						<input type="hidden" name="action" value="save_account_details" />
					</div>
					<?php do_action('woocommerce_edit_account_form_end'); ?>
				</div>
			</div>
		</form>
	</div>
</section>
<script>
	jQuery.validator.addMethod("customname", function(value, element) {
		return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
	}, "Please enter a valid name");
	jQuery.validator.addMethod("customphone", function(value, element) {
		return this.optional(element) || /^\+?\d{10,14}$/.test(value);
	}, "Please enter a valid phone number");
	jQuery.validator.addMethod("imageType", function(value, element) {
		return this.optional(element) || /\.(jpe?g|png|webp)$/i.test(value);
	}, "Please upload a valid image (JPEG, JPG, PNG, WEBP).");
	jQuery.validator.addMethod("futureDate", function(value, element) {
		var selectedDate = new Date(value);
		var today = new Date();
		today.setHours(0, 0, 0, 0);
		return this.optional(element) || selectedDate >= today;
	}, "Please select a future date as Wedding date.");
	jQuery('#woocommerce-EditAccountForm').validate({
		rules: {
			profile_picture: {
				imageType:  true
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
			},
			account_wedding_date : {
				futureDate: true
			}
		},
		messages: {
			profile_picture:{
				imageType:  "Please upload a valid image (JPEG, PNG, WEBP)."
			},
			account_first_name:{
				required:  "Please Enter Your First Name.",
				customphone:  "Please Enter valid Name",
				minlength : "Please Enter valid Name",
			},
			account_last_name: {
				required:  "Please Enter Your Last Name.",
				customphone:  "Please Enter Valid Name",
				minlength : "Please Enter valid Name",
			},
			account_email: {
				required: "Please Enter Your Email.",
				email: "Please Enter Valid Email",
			},
			account_phone:{
				// required: "Please Enter Your Phone.",
				customphone: "Please Enter Valid Phone Number",
			},
			account_partner_fname: {
				required: "Please Enter Your Partner's First Name.",
				customphone:  "Please Enter valid Name",
				minlength : "Please Enter valid Name",
			},
			account_partner_lname: {
				required: "Please Enter Your Partner's Last Name.",
				customphone:  "Please Enter valid Name",
				minlength : "Please Enter valid Name",
			},
			account_wedding_date : {
				futureDate: "Please select a future date as Wedding date."
			}
		},
		errorElement: "div",
		errorPlacement: function(error, element) {
			element.after(error);
		}
	});
</script>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
