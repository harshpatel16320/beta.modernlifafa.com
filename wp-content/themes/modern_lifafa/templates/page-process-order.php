<?php
/*
Template Name: Process Order
*/

get_header();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Log raw post data
    error_log('Raw POST data: ' . print_r($_POST, true));

    try {
        $required_fields = [
            'billing_first_name' => __('Billing first name is required.', 'woocommerce'),
            'billing_last_name'  => __('Billing last name is required.', 'woocommerce'),
            'billing_email'      => __('Billing email is required.', 'woocommerce'),
            'billing_address_1'  => __('Billing address is required.', 'woocommerce'),
            'billing_city'       => __('Billing city is required.', 'woocommerce'),
            'billing_postcode'   => __('Billing postcode is required.', 'woocommerce'),
            'billing_country'    => __('Billing country is required.', 'woocommerce'),
            'shipping_address_1' => __('Shipping address is required.', 'woocommerce'),
            'shipping_city'      => __('Shipping city is required.', 'woocommerce'),
            'shipping_postcode'  => __('Shipping postcode is required.', 'woocommerce'),
            'payment_method'     => __('Payment method is required.', 'woocommerce'),
        ];

        foreach ($required_fields as $field => $error_message) {
            if (empty($_POST[$field])) {
                wc_add_notice($error_message, 'error');
                wp_redirect(wc_get_checkout_url());
                exit;
            }
        }

        if (!isset($_POST['terms'])) {
            wc_add_notice(__('You must accept the terms and conditions.', 'woocommerce'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        if (WC()->cart->is_empty()) {
            wc_add_notice(__('Your cart is empty.', 'woocommerce'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        $payment_method = sanitize_text_field($_POST['payment_method']);

        // Billing & Shipping
        $billing = [
            'first_name' => sanitize_text_field($_POST['billing_first_name']),
            'last_name'  => sanitize_text_field($_POST['billing_last_name']),
            'email'      => sanitize_email($_POST['billing_email']),
            'phone'      => sanitize_text_field($_POST['billing_phone']),
            'address_1'  => sanitize_text_field($_POST['billing_address_1']),
            'address_2'  => sanitize_text_field($_POST['billing_address_2']),
            'city'       => sanitize_text_field($_POST['billing_city']),
            'state'      => sanitize_text_field($_POST['billing_state']),
            'postcode'   => sanitize_text_field($_POST['billing_postcode']),
            'country'    => 'IN',
        ];

        $shipping = [
            'first_name' => sanitize_text_field($_POST['shipping_first_name']),
            'last_name'  => sanitize_text_field($_POST['shipping_last_name']),
            'address_1'  => sanitize_text_field($_POST['shipping_address_1']),
            'address_2'  => sanitize_text_field($_POST['shipping_address_2']),
            'city'       => sanitize_text_field($_POST['shipping_city']),
            'state'      => sanitize_text_field($_POST['shipping_state']),
            'postcode'   => sanitize_text_field($_POST['shipping_postcode']),
            'country'    => 'IN',
        ];

        $customer_id = get_current_user_id();
        $order = wc_create_order(['customer_id' => $customer_id]);
        $platform_fee_amount = 0;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'] ?? 0;
            $quantity = $cart_item['quantity'];
            $custom_price = null;

            if (has_term('cash', 'product_cat', $product_id)) {
                $custom_price = isset($cart_item['custom_price']) ? floatval($cart_item['custom_price']) : $product->get_price();
                $product_total = $custom_price * $quantity;
                $fee = $product_total * 0.025;
                $platform_fee_amount += $fee;
                error_log("Cash product: ID $product_id, Fee: $fee");
            } else {
                $custom_price = $product->get_price();
                error_log("Non-cash product: ID $product_id, Price: $custom_price");
            }

            // Convert GST inclusive price to exclusive price before adding to manual order
            $tax_rates = WC_Tax::get_rates($product->get_tax_class());

            if (!empty($tax_rates)) {
                $taxes = WC_Tax::calc_inclusive_tax($custom_price, $tax_rates);
                $tax_amount = array_sum($taxes);

                $net_price = $custom_price - $tax_amount;
            } else {
                $net_price = $custom_price;
            }

            $order_item_id = $order->add_product($product, $quantity, [
                'subtotal' => $net_price * $quantity,
                'total'    => $net_price * $quantity,
            ]);

            if (has_term('cash', 'product_cat', $product_id) && !empty($cart_item['custom_price'])) {
                wc_add_order_item_meta($order_item_id, 'custom_price', floatval($cart_item['custom_price']));
            }

            if (!empty($cart_item['gift_for'])) {
                wc_add_order_item_meta($order_item_id, 'gift_for', sanitize_text_field($cart_item['gift_for']));
            }
        }

        if ($platform_fee_amount > 0) {
            error_log("Final platform fee to apply: $platform_fee_amount");
            $fee_item = new WC_Order_Item_Fee();
            $fee_item->set_name('Platform Fee');
            $fee_item->set_amount($platform_fee_amount);
            $fee_item->set_total($platform_fee_amount);
            $fee_item->set_tax_status('none');
            $order->add_item($fee_item);
        }


        $order->set_address($billing, 'billing');
        $order->set_address($shipping, 'shipping');

        // Set shipping location (VERY IMPORTANT)
        WC()->customer->set_shipping_country($shipping['country']);
        WC()->customer->set_shipping_state($shipping['state']);
        WC()->customer->set_shipping_postcode($shipping['postcode']);
        WC()->customer->set_shipping_city($shipping['city']);
        WC()->customer->set_shipping_address($shipping['address_1']);
        WC()->customer->set_shipping_address_2($shipping['address_2']);

        // Recalculate shipping
        WC()->cart->calculate_shipping();
        $packages = WC()->shipping()->get_packages();

        if (!empty($packages)) {
            foreach ($packages as $package) {
                if (!empty($package['rates'])) {
                    foreach ($package['rates'] as $rate_id => $rate) {

                        // Take ANY available shipping (first one)
                        $shipping_item = new WC_Order_Item_Shipping();
                        $shipping_item->set_method_title($rate->get_label());
                        $shipping_item->set_method_id($rate->get_method_id());
                        $shipping_item->set_total($rate->get_cost());

                        $order->add_item($shipping_item);

                        break 2; // stop after first shipping found
                    }
                }
            }
        }

        // Now calculate totals
        $order->calculate_taxes();
        $order->calculate_totals();

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $order->set_payment_method($payment_method);

        if (isset($available_gateways[$payment_method])) {
            $order->set_payment_method_title($available_gateways[$payment_method]->get_title());
        } else {
            $order->set_payment_method_title(__('Unknown Payment Method', 'woocommerce'));
        }

        // Extra Meta
        $gift_message = sanitize_textarea_field($_POST['gift_message'] ?? '');
        $deliver_to_couple = isset($_POST['deliver_to_couple']) ? 'yes' : 'no';

        $order->update_meta_data('gift_message', $gift_message);
        $order->update_meta_data('deliver_to_couple', $deliver_to_couple);

        $order->save();
        
        do_action( 'woocommerce_checkout_create_order', $order, [] );
        do_action( 'woocommerce_checkout_update_order_meta', $order->get_id(), [] );
        do_action( 'woocommerce_new_order', $order->get_id(), $order );
        do_action( 'woocommerce_checkout_order_processed', $order->get_id(), [], $order );

        // DOKAN ORDER SYNC (SUB-ORDERS)
        if ( ! function_exists( 'dokan_sync_insert_order' ) ) {
            error_log( 'Dokan sync function not available. Is Dokan Pro active?' );
        } else {
            dokan_sync_insert_order( $order->get_id() );
        }

        $order->calculate_totals();
        $order->save();

        // if (!function_exists('dokan_sync_insert_order')) {
        //     error_log('Dokan sync function not available. Is Dokan Pro active?');
        // }

        // // Dokan order sync
        // if (function_exists('dokan_sync_insert_order')) {
        //     dokan_sync_insert_order($order->get_id());
        // }

        // Process Payment
        if (isset($available_gateways[$payment_method])) {
            $payment_result = $available_gateways[$payment_method]->process_payment($order->get_id());

            if ($payment_result['result'] === 'success') {
                
                // Redirect to thank you page (order-received)
                if (!empty($payment_result['redirect'])) {
                    // Use payment gateway's redirect if available (e.g., external payment page)
                    wp_safe_redirect($payment_result['redirect']);
                } else {
                    // Default redirect to order-received (thank you) page
                    wp_safe_redirect($order->get_checkout_order_received_url());
                }
                exit;
            } else {
                wc_add_notice(__('Payment failed. Please try again.', 'woocommerce'), 'error');
                wp_redirect(wc_get_checkout_url());
                exit;
            }
        } else {
            wc_add_notice(__('Selected payment method is not available.', 'woocommerce'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }

    } catch (Exception $e) {
        error_log('Order Processing Error: ' . $e->getMessage());
        wc_add_notice(__('An unexpected error occurred. Please try again later.', 'woocommerce'), 'error');
        wp_redirect(wc_get_checkout_url());
        exit;
    }
}
?>
<h2>Processing your order...</h2>
<?php get_footer(); ?>
