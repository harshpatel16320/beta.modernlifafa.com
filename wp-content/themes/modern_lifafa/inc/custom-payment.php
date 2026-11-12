<?php
// 28-01-2025 razorpay intigration
require_once ABSPATH . '/vendor/autoload.php';
use Razorpay\Api\Api;
function transfer_to_user_bank($user_id, $amount, $user_type)
{
	$api_key = 'rzp_test_hoZNDa3Te8UQZN';
	$api_secret = 'HDjsb2gsTRJeMxxrG1vfz5iz';
	$fund_account_id = get_user_meta($user_id, 'razorpay_fund_account_id', true);
	if (!$fund_account_id) {
		return new WP_Error('no_fund_account', 'Fund account not found for user.');
	}
	$payout_data = [
		'account_number' => '2323230060920015',
		'fund_account_id' => $fund_account_id,
		'amount' => $amount * 100,
		'currency' => 'INR',
		'mode' => 'IMPS',
		'purpose' => 'payout',
		'narration' => 'Payment for services rendered',
		'queue_if_low_balance' => true,
		'reference_id' => 'order_' . time(),
	];
	try {
		$payout_response = razorpay_api_request("https://api.razorpay.com/v1/payouts", $payout_data, $api_key, $api_secret);
		if (!$payout_response || empty($payout_response['id'])) {
			throw new Exception("Failed to create payout.");
		}
		return $payout_response;
	} catch (Exception $e) {
		return new WP_Error('payout_error', $e->getMessage());
	}
}

function create_bank_fund_account($user_id, $user_type)
{
	error_log("Starting fund account creation for user ID: $user_id");
	$api_key = 'rzp_test_hoZNDa3Te8UQZN';
	$api_secret = 'HDjsb2gsTRJeMxxrG1vfz5iz';
	$user = get_user_by('id', $user_id);
	if (!$user) {
		error_log("User not found for ID: $user_id");
		return new WP_Error('user_not_found', 'User not found.');
	}
	if ($user_type == 'vendor') {
		$profile_settings = get_user_meta($user_id, 'dokan_profile_settings', true);
		if (!is_array($profile_settings) || empty($profile_settings['payment'])) {
			error_log("Profile settings or payment data is missing for vendor ID: " . $vendor_id);
			$payment_data = []; // Set an empty array to avoid further errors
		} else {
			$payment_data = $profile_settings['payment'];
		}
		$bank_details = $payment_data['bank'];
		$bank_account = isset($bank_details['ac_number']) ? $bank_details['ac_number'] : '';
		$ifsc = isset($bank_details['routing_number']) ? $bank_details['routing_number'] : '';
	} else {
		$bank_details = get_user_meta($user_id, 'bank_name', true);
		$bank_account = get_user_meta($user_id, 'account_number', true);
		$ifsc = get_user_meta($user_id, 'bank_ifsc', true);
	}
	// $payment_data = maybe_unserialize($profile_settings['payment']);
	if (!isset($bank_details)) {
		error_log("No bank details found for user ID: $user_id");
		return new WP_Error('no_bank_details', 'Bank details not found.');
	}
	$user_phone = isset($profile_settings['phone']) ? $profile_settings['phone'] : '';
	$user_name = $user->first_name . ' ' . $user->last_name;
	;
	$user_email = $user->user_email;
	try {
		error_log("Creating contact for user ID: $user_id");
		$contact_data = [
			'name' => $user_name,
			'email' => $user_email,
			'contact' => $user_phone,
			'type' => $user_type,
		];
		$contact_response = razorpay_api_request("https://api.razorpay.com/v1/contacts", $contact_data, $api_key, $api_secret);

		if (!$contact_response || empty($contact_response['id'])) {
			throw new Exception("Failed to create contact.");
		}
		error_log("Contact created successfully: " . print_r($contact_response, true));
		error_log("Creating fund account for contact ID: " . $contact_response['id']);
		$fund_account_data = [
			'contact_id' => $contact_response['id'],
			'account_type' => 'bank_account',
			'bank_account' => [
				'name' => $user_name,
				'account_number' => $bank_account,
				'ifsc' => $ifsc,
			]
		];
		$fund_account_response = razorpay_api_request("https://api.razorpay.com/v1/fund_accounts", $fund_account_data, $api_key, $api_secret);
		error_log(print_r($fund_account_response));
		if (!$fund_account_response || empty($fund_account_response['id'])) {
			throw new Exception("Failed to create fund account.");
		}
		error_log("Fund account created successfully: " . print_r($fund_account_response, true));
		update_user_meta($user_id, 'razorpay_fund_account_id', $fund_account_response['id']);
		error_log("Fund account ID saved for user ID: $user_id");
		return $fund_account_response;

	} catch (Exception $e) {
		error_log("Error creating fund account for user ID $user_id: " . $e->getMessage());
		return new WP_Error('fund_account_error', $e->getMessage());
	}
}

function razorpay_api_request($url, $data, $api_key, $api_secret)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json'
	]);
	curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . $api_secret);
	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($http_code == 200 || $http_code == 201) {
		return json_decode($response, true);
	} else {
		error_log("Razorpay API Request Failed: " . $response);
		return false;
	}
}

function process_transfer($user_id, $amount, $user_type)
{
	error_log("Processing transfer for user ID: $user_id, amount: $amount");
	$user_info = get_userdata($user_id);
	$user_email = $user_info ? $user_info->user_email : '';
	$user_name = $user_info ? $user_info->display_name : '';
	$fund_account_id = get_user_meta($user_id, 'razorpay_fund_account_id', true);
	if (!$fund_account_id) {
		error_log("No fund account found for user ID: $user_id. Attempting to create a new one.");
		$fund_account = create_bank_fund_account($user_id, $user_type);
		if (is_wp_error($fund_account)) {
			error_log('Error creating fund account: ' . $fund_account->get_error_message());
			return $fund_account;
		}
		$fund_account_id = $fund_account['id'];
	}
	$result = transfer_to_user_bank($user_id, $amount, $user_types);
	if (is_wp_error($result)) {
		error_log('Payout failed: ' . $result->get_error_message());
		// send_payment_email($user_email, $user_name, $amount, 'failed', $result->get_error_message());
		return $result;
	}
	error_log("Payout successful for user ID: $user_id. Response: " . print_r($result, true));
	// send_payment_email($user_email, $user_name, $amount, 'success');
	return $result;
}

add_action('woocommerce_order_status_completed', 'process_transfer_on_order_complete', 10, 1);
function process_transfer_on_order_complete($order_id)
{
	$order = wc_get_order($order_id);
	if (!$order) {
		error_log("Order not found: Order ID - " . $order_id);
		return;
	}
	$payment_completed = get_post_meta($order_id, '_payment_completed', true);
	if ($payment_completed) {
		error_log("Payment already completed for Order ID - " . $order_id);
		return;
	}
	// $vendor_orders = [];
	// $couple_orders = [];
	// foreach ($order->get_items() as $item) {
	// 	$product_id = $item->get_product_id();
	// 	$product = $item->get_product();
	// 	if (!$product) continue;
	// 	$categories = wp_get_post_terms($product_id, 'product_cat');
	// 	$category_name = '';
	// 	if (!empty($categories)) {
	// 		foreach ($categories as $category) {
	// 			if ($category->slug === 'cash') {
	// 				$category_name = $category->name;
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	if (!empty($category_name)) {
	// 		error_log("Skipping product in 'cash' category: Product ID - " . $product_id);
	// 		continue;
	// 	}
	// 	$vendor_id = get_post_field('post_author', $product->get_id());
	// 	if (!$vendor_id) {
	// 		error_log("Vendor ID not found for Product ID - " . $product_id);
	// 		continue;
	// 	}
	// 	$admin_percentage = dokan_get_option('admin_percentage', 'dokan_selling', 5);
	// 	$fixed_commission = get_option('dokan_fixed_commission', 10);
	// 	$product_total = $item->get_total();
	// 	$admin_commission = ($admin_percentage / 100) * $product_total + $fixed_commission;
	// 	$vendor_earning = $product_total - $admin_commission;

	// 	if (!isset($vendor_orders[$vendor_id])) {
	// 		$vendor_orders[$vendor_id] = 0;
	// 	}
	// 	$vendor_orders[$vendor_id] += $vendor_earning;

	// 	error_log("Vendor Earnings Updated: Vendor ID - $vendor_id | Amount - " . $vendor_orders[$vendor_id]);
	// }
	// $all_transfers_successful = true;
	// foreach ($vendor_orders as $vendor_id => $amount) {
	// 	error_log("Processing Transfer: Vendor ID - $vendor_id | Amount - $amount");
	// 	$result = process_transfer($vendor_id, $amount, "vendor");
	// 	$status = 'success';
	// 	$error_message = '';
	// 	if (is_wp_error($result)) {
	// 		$all_transfers_successful = false;
	// 		$status = 'failed';
	// 		$error_message = $result->get_error_message();
	// 		error_log("Transfer failed for Vendor ID: $vendor_id. Error: " . $result->get_error_message());
	// 	} else {
	// 		error_log("Transfer successful for Vendor ID: $vendor_id | Amount - $amount");
	//         // if ($vendor_email) {
	//         //     send_payment_email($vendor_email, $vendor_name, $amount, $order_id);
	//         // }
	// 	}
	// 	$vendor = get_user_by('ID', $vendor_id);
	// 	$vendor_email = $vendor ? $vendor->user_email : '';
	// 	$vendor_name = $vendor ? $vendor->display_name : 'Vendor';
	// 	if ($vendor_email) {
	// 		send_payment_email($vendor_email, $vendor_name, $amount, $order_id, $status, $error_message);
	// 	}
	// }
	// if ($all_transfers_successful) {
	// 	update_post_meta($order_id, '_payment_completed', 1);
	// 	error_log("Payment marked as completed for Order ID - " . $order_id);
	// } else {
	// 	error_log("Some transfers failed for Order ID - " . $order_id);
	// }

	$vendor_orders = [];
	$couple_orders = [];
	foreach ($order->get_items() as $item) {
		$product_id = $item->get_product_id();
		$product = $item->get_product();
		if (!$product)
			continue;
		$categories = wp_get_post_terms($product_id, 'product_cat');
		$category_name = '';
		if (!empty($categories)) {
			foreach ($categories as $category) {
				if ($category->slug === 'cash') {
					$category_name = $category->name;
					break;
				}
			}
		}
		if (!empty($category_name)) {
			error_log("Skipping product in 'cash' category: Product ID - " . $product_id);
			continue;
		}
		$vendor_id = get_post_field('post_author', $product->get_id());
		if (!$vendor_id) {
			error_log("Vendor ID not found for Product ID - " . $product_id);
			continue;
		}
		$gift_status = get_post_meta($product_id, '_gift_status', true);
		if (empty($gift_status)) {
			//wc_add_notice("No action performed from the user for Product ID - $product_id. Please put the order in processing status.", 'error');
			continue;
		}
		$admin_percentage = dokan_get_option('admin_percentage', 'dokan_selling', 5);
		$fixed_commission = get_option('dokan_fixed_commission', 10);
		$product_total = $item->get_total();
		$admin_commission = ($admin_percentage / 100) * $product_total + $fixed_commission;
		$final_amount = $product_total - $admin_commission;
		if ($gift_status === 'accept') {
			if (!isset($vendor_orders[$vendor_id])) {
				$vendor_orders[$vendor_id] = 0;
			}
			$vendor_orders[$vendor_id] += $final_amount;
			error_log("Vendor Earnings Updated: Vendor ID - $vendor_id | Amount - " . $vendor_orders[$vendor_id]);
		} elseif ($gift_status === 'reject') {
			$couple_user_id = get_post_meta($product_id, '_gift_for', true);
			if (!$couple_user_id) {
				error_log("Couple user ID not found for rejected Product ID - " . $product_id);
				continue;
			}
			if (!isset($couple_orders[$couple_user_id])) {
				$couple_orders[$couple_user_id] = 0;
			}
			$couple_orders[$couple_user_id] += $final_amount;
			error_log("Couple Refund Updated: Couple User ID - $couple_user_id | Amount - " . $couple_orders[$couple_user_id]);
		}
	}
	$all_transfers_successful = true;
	foreach ($vendor_orders as $vendor_id => $amount) {
		error_log("Processing Transfer to Vendor: Vendor ID - $vendor_id | Amount - $amount");
		$result = process_transfer($vendor_id, $amount, "vendor");
		handle_transfer_result($vendor_id, $amount, $order_id, $result, "vendor");
	}
	foreach ($couple_orders as $couple_user_id => $amount) {
		error_log("Processing Refund to Couple: User ID - $couple_user_id | Amount - $amount");
		$result = process_transfer($couple_user_id, $amount, "customer");
		handle_transfer_result($couple_user_id, $amount, $order_id, $result, "customer");
	}
	if ($all_transfers_successful) {
		update_post_meta($order_id, '_payment_completed', 1);
		error_log("Payment marked as completed for Order ID - " . $order_id);
	} else {
		error_log("Some transfers failed for Order ID - " . $order_id);
	}
}


// send_payment_email($email, $vendor_name, $amount, $order_id, $status, $error_message = '');

function send_payment_email($email, $vendor_name, $amount, $order_id, $status, $error_message = '')
{
	$home_url = home_url();
	$subject = ($status == 'success') ? "Payment Processed for Order #$order_id" : "Payout Failed for Order #$order_id";
	$headers = ['Content-Type: text/html; charset=UTF-8'];
	$message = "<p>Dear <strong>{$vendor_name}</strong>,</p>";
	$message = " <!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8' />
    <meta http-equiv='X-UA-Compatible' content='IE=edge' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <title>You have received a new gift!</title>
</head>

<body style='background-color: #fff;margin: 0;'>
    <table style='width: 100%;border-collapse: collapse;max-width: 650px; margin: 0 auto; border: 1px solid #DD1265;'
        cellspacing='0' cellpadding='0' width='100%'>
        <tbody style='background-color: #fff;'>
            <tr style='background-color: #fff;'>
                <td style='background-color: #DD1265; padding: 20px 15px 20px; border-radius: 0 0 20px 20px;overflow: hidden;'
                    align='center'>
                    <img src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/modern-lifafa-logo.png'
                        alt='Modern Lifafa Logo' style='width: auto; height: 90px; vertical-align: middle;'>
                </td>
            </tr>
	<tr>
	<td style='text-align: left; padding: 32px 20px 5px;'>
	<p style='font-size: 16px; font-weight:400; color:#111111; margin:0;'>
	<strong style='color:#57543D;'>Hello {$vendor_name},</strong>
	</p>
	</td>
	</tr>
	<tr>
	<td style='text-align: left; padding: 5px 20px;'>";
	if ($status == 'success') {
		$message .= "
		<p style='font-size: 16px; font-weight: 400; color:#111111; margin:0;'>
		<strong style='color:#57543D;'>Modern-lifafa</strong> Payment Notification.
		</p>
		<p style='font-size: 16px; color:#111111; margin:0;'>We have successfully processed your payment for Order #$order_id.</p>
		<p style='font-size: 16px; color:#111111;'><strong>Amount Transferred:</strong> ₹" . number_format($amount, 2) . "</p>
		<p>Thank you for being a valued vendor with us.</p>";
	} else {
		$message .= "
		<p style='font-size: 16px; font-weight: 400; color:#111111; margin:0;'>
		<strong style='color:#57543D;'>Modern-lifafa</strong> Payout Failure Notification.
		</p>
		<p style='font-size: 16px; color:#111111; margin:0;'>Unfortunately, your payout of <strong>₹" . number_format($amount, 2) . "</strong> has failed.</p>
		<p style='font-size: 16px; color:#111111;'><strong>Error Message:</strong> {$error_message}</p>
		<p>Please check your payout details or contact support for further assistance.</p>";
	}
	$message .= "<p>Best Regards,<br>Modern Lifafa</p>";
	$message .= "
	</td>
	</tr>
	</tbody>
	<tr style='background-color: #DD1265;'>
            <td style='padding: 30px 20px; background: #FFC4D8; text-align: center; border-radius: 20px 20px 0px 0px;'>
                <p
                    style='font-size: 14px; color:#5C164C;font-weight: 500;margin: 0;font-family: Segoe UI,Helvetica,Ubuntu,sans-serif;'>
                    Follow Us</p>
                <a href='#'><img
                        src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/instagram.png'
                        alt='Instagram' style='width: 30px; height: 30px; margin-right: 10px;' /></a>
                <a href='#'><img
                        src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/facebook.png'
                        alt='Facebook' style='width: 30px; height: 30px;' /></a>
                <p style='font-size: 14px;line-height: 20px;color: #5C164C;font-weight: 400;margin: 0;'>Copyright © 2025
                    <span style='color: #5C164C; font-weight: 500;'>modernlifafa.</span> All Right Reserved
                </p>
            </td>
        </tr>
	</table>
	</body>
	</html>";
	error_log("Sending payment email to: $email | Name: $vendor_name | Amount: ₹$amount | Status: $status | Error: $error_message");
	$sent = wp_mail($email, $subject, $message, $headers);
	if ($sent) {
		error_log("Email sent successfully to: $email");
	} else {
		error_log("Failed to send email to: $email");
	}
}