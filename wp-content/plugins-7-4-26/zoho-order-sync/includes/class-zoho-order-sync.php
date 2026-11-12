<?php

class Zoho_Order_Sync {

    public static function init() {

        // update_option('zoho_access_token', '1000.140d95a336b8ec42dad996ea64dd5782.39f89d8ab032fbf8c4b13e90d459e8a4');
        // update_option('zoho_refresh_token', '1000.95e5b22fbb7da26d845f82b658f69d47.812c32d92b768ba7ed03da96bc325bb7');
        // Hook into WooCommerce 'thank you' page to send order to Zoho
        // add_action('woocommerce_thankyou', array( 'Zoho_Order_Sync', 'send_order_to_zoho' ), 10, 1);
        // add_action('init', array( 'Zoho_Order_Sync', 'send_order_to_zoho' ), 10, 1);
        add_action('woocommerce_order_status_changed', array( 'Zoho_Order_Sync', 'send_order_to_zoho' ), 10, 1);
        // add_action('woocommerce_order_status_changed', array( 'Zoho_Order_Sync', 'send_order_to_zoho_on_status_change' ), 10, 3);
        add_action('refresh_zoho_token_cron', array( 'Zoho_Order_Sync', 'refresh_zoho_access_token' ) );
        // Schedule the cron job to refresh Zoho access token
        if (!wp_next_scheduled('refresh_zoho_token_cron')) {
            wp_schedule_event(time(), 'hourly', 'refresh_zoho_token_cron');
        }
    }

    public static function activate() {
        // Actions to take upon plugin activation
    }

    public static function deactivate() {
        // Actions to take upon plugin deactivation
    }

    public static function send_order_to_zoho($order_id) {
        // $order_id  = 1443;
        // error_log('******************Called****************');
        if (!$order_id) {
            return;
        }
        $order = wc_get_order($order_id);
        $status = $order->get_status();
        if (!$order) {
            error_log('Order not found: ' . $order_id);
            return;
        }
        
        $items = $order->get_items();
        $shipping = [
            'address' => $order->get_shipping_address_1(),
            'city' => $order->get_shipping_city(),
            'state' => $order->get_shipping_state(),
            'zip' => $order->get_shipping_postcode(),
            'country' => $order->get_shipping_country(),
        ];
        $couple_name = 'Couple name';
        $line_items = [];
        // $total_product_price = 0;
        foreach ($items as $item) {
            $product_name = $item->get_name();
            $product_variation = $item->get_variation_id();
            $quantity = $item->get_quantity();
            $rate = $item->get_total() / $quantity;
            $couple_user_id = $item->get_meta('_gift_for');
            $total_product_price = $item->get_total();
            $line_items[] = [
                'name' => $product_name,
                'item_order' => $product_variation ?: 'N/A',
                'quantity' => $quantity,
                'rate' => $rate,
            ];
        }
        if ($couple_user_id) {
            $user = get_userdata($couple_user_id);
            if ($user) {
                $first_name = esc_attr($user->first_name);
                $partner_name = esc_attr(get_user_meta($couple_user_id, 'partner_fname', true));
                $couple_name = $first_name . ' & ' . $partner_name;
            } else {
                echo 'User not found.';
            }
        }

        $shipping_output = "Address: " . $order->get_shipping_address_1() . "\n";
        $shipping_output .= "City: " . $order->get_shipping_city() . "\n";
        $shipping_output .= "State: " . $order->get_shipping_state() . "\n";
        $shipping_output .= "ZIP: " . $order->get_shipping_postcode() . "\n";
        $shipping_output .= "Country: " . $order->get_shipping_country() . "\n";

        $vendor_id = get_post_field('post_author', $item->get_product_id());
        $vendor_name = get_user_meta($vendor_id, 'dokan_store_name', true);
        $profile_settings = get_user_meta($vendor_id, 'dokan_profile_settings', true);
        if (!empty($profile_settings) && is_string($profile_settings)) {
            $profile_settings = maybe_unserialize($profile_settings);
        }
        $payment_data = isset($profile_settings['payment']) ? $profile_settings['payment'] : null;
        $bank_details = $payment_data['bank'];
        $commission_settings = dokan_get_option( 'admin_percentage', 'dokan_selling', 5 );
		error_log( "Commission Settings: " . $commission_settings );

		$total_order_amount = $order->get_total();
    	$commission_amount = ( $total_order_amount * $commission_settings ) / 100;
    	 error_log("Calculated Vendor Commission: " . $commission_amount);
    	
    	// if ($commission_amount > $total_order_amount) {
	    //     $commission_amount = $total_order_amount; 
	    // }	
	    // $vendor_earnings = $total_order_amount - $commission_amount;
	    // error_log("Vendor Earnings: " . $vendor_earnings);
        // $vendor_details = "Vendor Name : " . $vendor_name . "\n";
        // // $vendor_details .= "Vendor Address : " . json_encode($vendor_address) . "\n";
        // $vendor_details .= "Vendor Phone : " . $profile_settings['phone'] . "\n"; 
        // $vendor_details .= "Vendor Bank Account No : " . $bank_details['ac_number'] . "\n";
        $vendor_phone = '';
        $vendor_ac_number = '';
        $vendor_output = '';
        if($vendor_id){
            $vendor_phone = isset($profile_settings['phone']) ? $profile_settings['phone'] : '';
            $bank_details = isset($profile_settings['payment']['bank']) ? $profile_settings['payment']['bank'] : [];
            $vendor_ac_number = isset($bank_details['ac_number']) ? $bank_details['ac_number'] : '';
        }
        $mode_of_payment = $order->get_payment_method_title();
        $total_amount = $order->get_total();
        $output = '';
        $i = 0;
        $product_output = '';
        foreach ($line_items as $item) {
            $i++;
            $product_output .= "Item".$i." Name: " . $item['name'] . "\n";
            $product_output .= "Item".$i." Quantity: " . $item['quantity'] . "\n";
            $product_output .= "Item".$i." Rate: " . $item['rate'] . "\n";
        }
        $data = [
            'Name'              => 'Order #' . $order_id,
            'Email'             => $order->get_billing_email(),
            'CoupleName'        => $couple_name,
            'VendorName'        => $vendor_name,
            'VendorPhone'       => $vendor_phone,
            'OrderStatus'       => $status,
            'VendorAccountNo'   => $vendor_ac_number,
            'SellerDetails'     => $vendor_output,
            'ShippingAddress'   => $shipping_output,
            'ProductName'       => implode(', ', array_column($line_items, 'name')),
            'OrderId'           => $order_id,
            'PaymentMode'       => $mode_of_payment,
            'ProductDetail'     => $product_output,
           	'Commission'        => $commission_amount,
			'TotalPrice'        => $total_product_price,
        ];
        // echo "called";
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // error_log(print_r($data));
        // Send data to Zoho
        Zoho_API::send_data_to_zoho($data);
    }

    public static function refresh_zoho_access_token() {
        Zoho_API::refresh_zoho_access_token();
    }
}
