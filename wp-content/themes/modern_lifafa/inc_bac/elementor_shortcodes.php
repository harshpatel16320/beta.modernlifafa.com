<?php


function custom_product_listing_shortcode($atts)
{
	ob_start();
	get_template_part('templates/product-listing');
	return ob_get_clean();
}
add_shortcode('custom_product_listing', 'custom_product_listing_shortcode');


// Footer social icon section shortcode

function modern_lifafa_copyright_shortcode()
{
	ob_start(); ?>

	<div class="copyright-section">
		<?php
		if (have_rows('social_share', 'option')):
			while (have_rows('social_share', 'option')):
				the_row();
				$image = get_sub_field('image');
				$link = get_sub_field('link');
				?>
				<div class="social-icons">
					<a href="<?php echo esc_url($link); ?>">
						<img src="<?php echo esc_url($image); ?>" alt="social-icon" />
					</a>
				</div>
				<?php
			endwhile;
		endif;
		?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('social_icon', 'modern_lifafa_copyright_shortcode');


// Shortcode for Couple Story
function display_couple_story()
{
    ob_start();

    $user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : get_current_user_id();
    if (!$user_id || !($user = get_userdata($user_id)))
        return '<p>User not found.</p>';

    $partner_fname = get_user_meta($user_id, 'partner_fname', true);
    $our_story = get_user_meta($user_id, 'our_story', true);

    if ($our_story) {
        echo '<h2 class="sec-sub-title">' . esc_html($user->first_name) . ' & ' . esc_html($partner_fname) . '</h2>';
        echo '<p>' . esc_html($our_story) . '</p>';
    }

    return ob_get_clean();
}
add_shortcode('couple_story', 'display_couple_story');


// Shortcode for Wedding Countdown
function wedding_countdown_shortcode()
{
    ob_start();

    $user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : get_current_user_id();
    if (!$user_id)
        return '<p>No user found.</p>';

    $wedding_date = get_user_meta($user_id, 'wedding_date', true);
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);

    if ($wedding_date) {
        $days_left = max(0, round((strtotime($wedding_date) - time()) / (60 * 60 * 24)));
        //print_r($days_left);
        if ($days_left > 0) {
            echo '<div class="days-counting-wrap"><h3>' . esc_html($days_left) . ' Days to go!</h3></div>';
        }
    }

    ?>
    <div class="user-profile-image">
        <picture>
            <img src="<?php echo esc_url($profile_picture ?: get_template_directory_uri() . '/assets/images/user-avtar.png'); ?>"
                alt="Profile Picture" width="160" height="160">
        </picture>
        <?php if(is_user_logged_in()){ ?>
            <div class="upload-file">
                <input type="file" class="woocommerce-Input woocommerce-Input--file input-file" name="profile_picture" id="profile_picture_input" accept="image/*" />
                <label for="file"><span class="icon-edit"></span></label>
                <span class="tooltip-info">Update Photo</span>
            </div>
        <?php } ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('wedding_countdown', 'wedding_countdown_shortcode');


// Shortcode for Total Amount Received
function total_amount_received_shortcode()
{
    ob_start();

    $user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : get_current_user_id();
    $orders = get_orders_by_user($user_id);

    $total_amount = 0;
    foreach ($orders as $order_id) {
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item) {
            if (in_array('cash', wp_get_post_terms($item->get_product_id(), 'product_cat', ['fields' => 'slugs']))) {
                $total_amount += $item->get_total();
            }
        }
    }
    ?>
    <div class="lifafa-box">
        <h3 class="lifafa-amount"><?php echo wc_price($total_amount); ?></h3>
        <p class="lifafa-subtitle">Total cash recieved</p>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('total_received_amount', 'total_amount_received_shortcode');

// Shortcode for Total Items Received
function total_items_received_shortcode()
{
    ob_start();
    $user_id = isset($_GET['original_user_id']) ? intval($_GET['original_user_id']) : get_current_user_id();
    $personalized_products = get_user_meta($user_id, '_personalized_products', true);
    if (!is_array($personalized_products)) {
        $personalized_products = [];
    }
    $product_count = count($personalized_products);
    // $orders = get_orders_by_user($user_id);
    // $count = 0;
    $orders = get_orders_by_user($user_id);
    $count = 0;
    foreach ($orders as $order_id) {
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item) {
            $count++;
        }
    }
    ?>
    <div class="lifafa-box">
        <h3 class="lifafa-amount"><?php echo $count . '/' . $product_count; ?></h3>
        <p class="lifafa-subtitle">Total Items Received</p>
        <?php if (is_user_logged_in()) { ?>
            <a href="<?php echo home_url('/received-gifts'); ?>" class="lifafa-details">See Details</a>
        <?php } ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('total_items_received', 'total_items_received_shortcode');

function custom_woocommerce_registration_form_shortcode()
{
    if (is_user_logged_in()) {
        return '<p>You are already registered and logged in.</p>';
    }

    ob_start();
    ?>
    <form class="woocommerce-form woocommerce-form-register register" enctype="multipart/form-data" method="post"
        id="woocommerce-form-register" <?php do_action('woocommerce_register_form_tag'); ?>>
        <div class="signing-inner">
            <?php do_action('woocommerce_register_form_start'); ?>
            <h2 class="heading-title">Sign up</h2>
            <div class="upload-side">
                <div class="upload-file">
                    <input type="file" id="file" class="register_file" name="profile_image" accept="image/*" hidden>
                    <label for="file">
                        <picture>
                            <img id="profile-image" class="add_image"
                                src="<?php echo home_url(); ?>/wp-content/uploads/2025/02/upload-1.png"
                                alt="upload-icon">
                        </picture>
                        <p>Upload Photo</p>
                    </label>
                </div>
            </div>
            <div class="input-form fg-one">
                <div class="form-group">
                    <div class="group-input">
                        <input type="text" onfocus="(this.type='date')" class="form-control" placeholder="Wedding Date"
                            name="wedding_date" id="reg_wedding_date" />
                    </div>
                </div>
            </div>
            <div class="form-side">
                <div class="input-form grid-two">
                    <div class="form-group">
                        <label for="name">First Name*</label>
                        <input type="text" placeholder="First Name*" class="form-control" name="billing_first_name"
                            id="reg_billing_first_name" required />
                    </div>
                    <div class="form-group">
                        <label for="name">Last Name*</label>
                        <input type="text" placeholder="Last Name*" class="form-control" name="billing_last_name"
                            id="reg_billing_last_name" required />
                    </div>
                    <div class="form-group">
                        <label for="name">Email*</label>
                        <input type="email" class="form-control" placeholder="Email*" name="email" id="reg_email"
                            required />
                    </div>
                    <div class="form-group">
                        <label for="name">Phone Number</label>
                        <input type="text" class="form-control" name="phone" placeholder="Phone Number" id="reg_phone" />
                    </div>
                    <div class="form-group">
                        <label for="name">Partner's First Name*</label>
                        <input type="text" class="form-control" placeholder="Partner's First Name*" name="partner_fname"
                            id="reg_partner_fname" required />
                    </div>
                    <div class="form-group">
                        <label for="name">Partner's Last Name*</label>
                        <input type="text" class="form-control" placeholder="Partner's Last Name*" name="partner_lname"
                            id="reg_partner_lname" required />
                    </div>
                    <div class="form-group">
                        <label for="name">Partner's Email (optional)</label>
                        <input type="email" class="form-control" placeholder="Partner's Email (optional)"
                            name="secondary_email" id="reg_secondary_email" />
                    </div>
                </div>
                <input type="hidden" class="form-control" name="role" value="registered_customer" />
            </div>
            <div class="input-form grid-two">
                <div class="form-group">
                    <div class="group-input">
                        <label for="name">Password*</label>
                        <div class="group-input-inner">
                            <input type="password" class="form-control" placeholder="Password*" name="password_1"
                                id="password_1" required />
                            <span class="input-action-btn password_hide_show" data-id="password_1">
                                <input type="hidden" id="show_icon" name="show_icon"
                                    value="<?php echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
                                <input type="hidden" id="hide_icon" name="hide_icon"
                                    value="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg"
                                        alt="open eye" width="24" height="24">
                                </picture>
                            </span>
                            <!-- <span class="input-action-btn password_hide_show" data-id="password_1">
                                <picture>
                                    <img src="<?php // echo get_template_directory_uri(); ?>/assets/images/close-eye.svg"
                                        alt="open eye" width="24" height="24">
                                </picture>
                            </span> -->
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="group-input">
                        <label for="name">Confirm Password*</label>
                        <div class="group-input-inner">
                            <input type="password" class="form-control input-text" placeholder="Confirm Password*"
                                name="password_2" id="password_2" required />
                            <!-- <span class="input-action-btn password_hide_show" data-id="password_2">
                                <picture>
                                    <img src="<?php //echo get_template_directory_uri(); ?>/assets/images/close-eye.svg"
                                        alt="open eye" width="24" height="24">
                                </picture>
                            </span> -->
                            <span class="input-action-btn password_hide_show" data-id="password_2">
                                <input type="hidden" id="show_icon" name="show_icon"
                                    value="<?php echo get_template_directory_uri(); ?>/assets/images/eye-open.svg">
                                <input type="hidden" id="hide_icon" name="hide_icon"
                                    value="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg">
                                <picture>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/close-eye.svg"
                                        alt="open eye" width="24" height="24">
                                </picture>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="input-form fg-one">
                <div class="form-group">
                    <label for="name">Your Story</label>
                    <textarea name="our_story" rows="7"
                        placeholder="Share a few heartfelt details about how you met, your dreams for the future, or any special anecdotes that reflect your journey as a couple—so your loved ones can be a part of your story!"
                        class="form-control"></textarea>
                </div>
            </div>
        </div>
            <div class="form-group w-100">[cf7sr-recaptcha]</div>
        <div class="form-button">
            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
            <button type="submit" class="woocommerce-Button button custom_button secondary_button" name="register">
                Submit
            </button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_woo_registration', 'custom_woocommerce_registration_form_shortcode');

function custom_search_box_shortcode()
{
    ob_start();
    ?>
    <div class="search-box-wrapper" style="display:none;">
        <div class="search-input-wrap">
            <div class="container">
                <div class="search-input">
                    <div class="" id="searchform">
                        <input type="text" autocomplete="off" id="s" class="form-control header_main_search"
                            placeholder="<?php echo esc_attr_x('Search …', 'placeholder'); ?>"
                            value="<?php echo get_search_query(); ?>"
                            title="<?php echo esc_attr_x('Search for:', 'label'); ?>" />
                        <button type="submit" style="display:none" role="button" class="btn btn-default right"><span
                                class="glyphicon glyphicon-search white"></span></button>
                    </div>
                    <button type="button" class="close-search-bar"><span class="icon-cross"></span></button>
                </div>
            </div>
        </div>
        <div class="search-results">
            <div class="container">
                <span class="results-title">Search results</span>
                <ul class="load-search-result"></ul>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_search_box', 'custom_search_box_shortcode');


function account_deletion_modal_shortcode()
{
    ob_start();
    ?>
    <div class="modal fade" id="accountDeletionModal" tabindex="-1" aria-labelledby="producturlModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="producturlModalLabel">Confirm Account Deletion</h2>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                    <div class="copy-input">
                        <input type="password" class="form-control" id="account-password" placeholder="Enter Password"
                            required>
                    </div>
                    <div class="buttonGroupWrap">
                        <button id="confirm-delete-btn"
                            class="custom_button add_external_product primary_button_outline btn btn-danger">
                            Confirm Delete
                        </button>
                        <button id="cancel-delete-btn"
                            class="custom_button add_external_product primary_button_outline btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('account_deletion_modal', 'account_deletion_modal_shortcode');


function get_current_user_id_shortcode()
{
    return get_current_user_id();
}
add_shortcode('current_user_id', 'get_current_user_id_shortcode');


// Banner image display shortcode  
function get_banner_images_by_user_id()
{
    ?>
    <div class="modal fade" id="accountbannerModal" tabindex="-1" aria-labelledby="producturlModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="producturlModalLabel">Choose Your Banner Image</h2>
                </div>
                <div class="modal-body">
                    <div class="input-form banner-wrapper">
                        <?php
                        $user_id = get_current_user_id();
                        $banner_images = get_option('banner_images', []);
                        $selected_banner = get_user_meta($user_id, 'selected_banner', true);

                        foreach ($banner_images as $image_id):
                            $image_url = wp_get_attachment_url($image_id);
                            if (!$image_url)
                                continue;
                            ?>
                            <label class="radio">
                                <input type="radio" name="user_banner" value="<?php echo esc_url($image_url); ?>" <?php checked($selected_banner, $image_url); ?>>
                                <img src="<?php echo esc_url($image_url); ?>" width="100">
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="buttonGroupWrap">
                        <button id="update_banner_btn" class="custom_button secondary_button">Update Banner</button>
                    </div>
                    <p id="update_banner_message" style="color: green; display: none;">Banner updated successfully!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // banner image in my account page JS
        jQuery(document).ready(function ($) {
            $('#update_banner_btn').on('click', function (e) {
                e.preventDefault();

                let selectedBanner = $('input[name="user_banner"]:checked').val();
                if (!selectedBanner) {
                    alert('Please select a banner image.');
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: "save_user_banner",
                        banner_url: selectedBanner,
                        security: "<?php echo wp_create_nonce('save_user_banner_nonce'); ?>"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#update_banner_message').show().delay(2000).fadeOut();
                            setTimeout(function () {
                                $('.elementor-popup-modal').fadeOut(); // Hide popup
                                $('body').removeClass('scroll-hide').css('overflow', '');
                                location.reload();
                            }, 2000);

                        } else {
                            alert('Error saving banner.');
                        }
                    }
                });
            });
            $('input[name="user_banner"]').on('change', function () {
                $('input[name="user_banner"]').prop('checked', false); // Uncheck all
                $(this).prop('checked', true); // Check only the selected one
            });
        });
    </script>
    <?php
}
add_shortcode('banner_images', 'get_banner_images_by_user_id');


// Change Password
function custom_change_password_form()
{
    if (!is_user_logged_in()) {
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["custom_save_password"])) {
        // Verify Nonce for Security
        if (!isset($_POST['custom_save_password_nonce']) || !wp_verify_nonce($_POST['custom_save_password_nonce'], 'custom_save_password_action')) {
            wc_add_notice("Security check failed. Please try again.", "error");
        } else {
            $current_password = !empty($_POST["password_current"]) ? sanitize_text_field($_POST["password_current"]) : '';
            $new_password = !empty($_POST["password_1"]) ? sanitize_text_field($_POST["password_1"]) : '';
            $confirm_password = !empty($_POST["password_2"]) ? sanitize_text_field($_POST["password_2"]) : '';
            $user_id = get_current_user_id();
            $user = get_userdata($user_id);

            // Check if current password is correct
            if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
                wc_add_notice("Old password is incorrect.", "error");
            } elseif ($new_password !== $confirm_password) {
                wc_add_notice("New passwords do not match.", "error");
            } elseif (empty($new_password) || strlen($new_password) < 6) {
                wc_add_notice("New password must be at least 6 characters.", "error");
            } else {

                wp_set_password($new_password, $user_id);
                wc_add_notice("Password changed successfully!", "success");

                // Destroy WooCommerce session
                if (class_exists('WC_Session_Handler') && isset(WC()->session)) {
                    WC()->session->destroy_session();
                }

                // Log out user
                wp_logout();
                wp_clear_auth_cookie();

                // Expire all authentication cookies
                setcookie(LOGGED_IN_COOKIE, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                setcookie(AUTH_COOKIE, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                setcookie(SECURE_AUTH_COOKIE, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

                // Force WordPress to redirect immediately
                // $redirect_url = wc_get_page_permalink('myaccount');
                $redirect_url = add_query_arg('password_change', 'true', wc_get_page_permalink('myaccount'));


                if (!headers_sent()) {
                    wp_safe_redirect($redirect_url);
                    exit;
                } else {
                    echo "<script>window.location.href='" . esc_url($redirect_url) . "';</script>";
                    exit;
                }
            }
        }
    }

    ob_start();
    ?>
    <section class="change-password-section contact-form-section">
        <div class="container">
            <div class="top-banner-section">
                <div class="banner-inner">
                    <h1 class="banner-title">Change Password</h1>
                </div>
            </div>
            <div class="change-password-inner">
                <?php wc_print_notices(); ?>
                <form method="post" class="common-form-wrap woocommerce-EditAccountForm edit-account"
                    id="woocommerce-EditAccountForm">
                    <div class="input-form fg-three">
                        <?php
                        $fields = [
                            'password_current' => 'Old Password',
                            'password_1' => 'New Password',
                            'password_2' => 'Confirm Password'
                        ];
                        foreach ($fields as $id => $placeholder): ?>
                            <div class="form-group">
                                <div class="group-input">
                                    <input type="password"
                                        class="woocommerce-Input woocommerce-Input--text input-text form-control"
                                        name="<?php echo esc_attr($id); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"
                                        id="<?php echo esc_attr($id); ?>"
                                        autocomplete="<?php echo $id === 'password_current' ? 'current-password' : 'new-password'; ?>" />
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="button-wrap">
                        <?php wp_nonce_field('custom_save_password_action', 'custom_save_password_nonce'); ?>
                        <button type="submit" class="custom_button secondary_button woocommerce-Button button"
                            name="custom_save_password"><?php esc_html_e('Update Password', 'woocommerce'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('change_password_form', 'custom_change_password_form');