<?php

defined('ABSPATH') || exit;

class ML_RPX_PAYOUT
{
    /**
 * Create RazorpayX payout for one specific Lifafa item.
 *
 * Transfer Now uses this method.
 *
 * @param int $order_id
 * @param int $item_id
 * @return array|WP_Error
 */
public static function create_for_item($order_id, $item_id)
{
    $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error(
                'invalid_order',
                'WooCommerce order not found.'
            );
        }

        $item = $order->get_item($item_id);

        if (!$item) {
            return new WP_Error(
                'invalid_item',
                'Gift item not found.'
            );
        }

        /*
        * -------------------------------------------------
        * 1. Make sure this is a Lifafa / cash item
        * -------------------------------------------------
        */

        $product = $item->get_product();

        if (!$product) {
            return new WP_Error(
                'invalid_product',
                'Gift product not found.'
            );
        }

        $product_id = $product->get_id();

        if (!has_term('cash', 'product_cat', $product_id)) {
            return new WP_Error(
                'not_cash_item',
                'This gift is not eligible for cash payout.'
            );
        }

        /*
        * -------------------------------------------------
        * 2. Make sure this item belongs to the current couple
        * -------------------------------------------------
        */

        $couple_user_id = (int) $item->get_meta('gift_for', true);

        if (!$couple_user_id) {
            $couple_user_id = (int) $order->get_meta(
                '_couple_user_id'
            );
        }

        if (!$couple_user_id) {
            return new WP_Error(
                'couple_user_missing',
                'Couple user ID is not available.'
            );
        }

        /*
        * -------------------------------------------------
        * 3. Get RazorpayX Fund Account
        * -------------------------------------------------
        */

        $fund_account_id = get_user_meta(
            $couple_user_id,
            '_ml_rpx_fund_account_id',
            true
        );

        error_log(
            'ML RPX: Transfer Now | Order #' .
            $order_id .
            ' | Item #' .
            $item_id .
            ' | Couple #' .
            $couple_user_id .
            ' | Fund Account=' .
            $fund_account_id
        );

        if (empty($fund_account_id)) {
            return new WP_Error(
                'fund_account_missing',
                'Couple does not have a RazorpayX fund account.'
            );
        }

        /*
        * -------------------------------------------------
        * 4. Prevent duplicate payout for this item
        * -------------------------------------------------
        */

        $existing_payout_id = $item->get_meta(
            '_ml_rpx_item_payout_id',
            true
        );

        if (!empty($existing_payout_id)) {
            return new WP_Error(
                'payout_already_created',
                'This Lifafa has already been transferred.'
            );
        }

        /*
        * Also check gift status.
        */
        $gift_status = $item->get_meta(
            '_gift_status',
            true
        );

        if ($gift_status === 'confirm') {
            return new WP_Error(
                'gift_already_transferred',
                'This Lifafa has already been transferred.'
            );
        }

        /*
        * -------------------------------------------------
        * 5. Calculate ONLY this item's amount
        * -------------------------------------------------
        */

        $custom_price = $item->get_meta(
            'custom_price',
            true
        );

        /*
        * Fallback to product custom_price.
        */
        if ($custom_price === '' || $custom_price === null) {
            $custom_price = get_post_meta(
                $product_id,
                'custom_price',
                true
            );
        }

        if ($custom_price === '' || $custom_price === null) {
            return new WP_Error(
                'payout_amount_missing',
                'Lifafa amount could not be determined.'
            );
        }

        /*
        * Remove currency symbols / commas.
        */
        $custom_price = preg_replace(
            '/[^\d.]/',
            '',
            (string) $custom_price
        );

        $custom_price = (float) $custom_price;

        if ($custom_price <= 0) {
            return new WP_Error(
                'invalid_payout_amount',
                'Lifafa payout amount must be greater than zero.'
            );
        }

        $quantity = max(
            1,
            (int) $item->get_quantity()
        );

        $payout_amount = $custom_price * $quantity;

        error_log(
            'ML RPX: Transfer Now | Item #' .
            $item_id .
            ' | custom_price=' .
            $custom_price .
            ' | quantity=' .
            $quantity .
            ' | payout=₹' .
            $payout_amount
        );

        /*
        * -------------------------------------------------
        * 6. Create/reuse idempotency key
        * -------------------------------------------------
        */

        $idempotency_key = $item->get_meta(
            '_ml_rpx_item_payout_idempotency_key',
            true
        );

        if (empty($idempotency_key)) {

            $idempotency_key = 'ML_ITEM_' .
                $order_id .
                '_' .
                $item_id .
                '_' .
                substr(
                    md5(uniqid('', true)),
                    0,
                    12
                );

            $item->update_meta_data(
                '_ml_rpx_item_payout_idempotency_key',
                $idempotency_key
            );

            $item->save();
        }

        error_log(
            'ML RPX: Transfer Now | Item #' .
            $item_id .
            ' | Idempotency=' .
            $idempotency_key
        );

        /*
        * -------------------------------------------------
        * 7. Reference ID
        * -------------------------------------------------
        */

        $reference_id = 'ML_ITEM_' .
            $order_id .
            '_' .
            $item_id;

        $reference_id = substr(
            sanitize_text_field($reference_id),
            0,
            40
        );

        /*
        * -------------------------------------------------
        * 8. RazorpayX payout data
        * -------------------------------------------------
        */

        $data = array(
            'account_number' => ML_RPX_ACCOUNT_NUMBER,

            'fund_account_id' => $fund_account_id,

            /*
            * RazorpayX expects paise.
            */
            'amount' => (int) round(
                $payout_amount * 100
            ),

            'currency' => 'INR',

            'mode' => 'IMPS',

            'purpose' => 'payout',

            'queue_if_low_balance' => true,

            'reference_id' => $reference_id,

            'narration' => 'Modern Lifafa Gift',
        );

        error_log(
            'ML RPX: Transfer Now payout request: ' .
            wp_json_encode(
                array(
                    'order_id' => $order_id,
                    'item_id' => $item_id,
                    'couple_user_id' => $couple_user_id,
                    'fund_account_id' => $fund_account_id,
                    'amount' => $payout_amount,
                    'amount_paise' => $data['amount'],
                    'reference_id' => $reference_id,
                )
            )
        );

        /*
        * -------------------------------------------------
        * 9. Call RazorpayX
        * -------------------------------------------------
        */

        $response = ML_RPX_API::request(
            'POST',
            '/payouts',
            $data,
            array(
                'X-Payout-Idempotency' => $idempotency_key,
            )
        );

        if (is_wp_error($response)) {

            error_log(
                'ML RPX: Transfer Now payout API error | Order #' .
                $order_id .
                ' | Item #' .
                $item_id .
                ' | ' .
                $response->get_error_message()
            );

            return $response;
        }

        /*
        * -------------------------------------------------
        * 10. Validate RazorpayX response
        * -------------------------------------------------
        */

        if (empty($response['id'])) {

            error_log(
                'ML RPX: Transfer Now invalid RazorpayX response: ' .
                wp_json_encode($response)
            );

            return new WP_Error(
                'payout_failed',
                'RazorpayX payout was not created.'
            );
        }

        /*
        * -------------------------------------------------
        * 11. Save payout details ON THE ITEM
        * -------------------------------------------------
        */

        $item->update_meta_data(
            '_ml_rpx_item_payout_id',
            sanitize_text_field($response['id'])
        );

        $item->update_meta_data(
            '_ml_rpx_item_payout_amount',
            $payout_amount
        );

        $item->update_meta_data(
            '_ml_rpx_item_payout_couple_user_id',
            $couple_user_id
        );

        $item->update_meta_data(
            '_ml_rpx_item_payout_fund_account_id',
            sanitize_text_field($fund_account_id)
        );

        $item->update_meta_data(
            '_ml_rpx_item_payout_status',
            sanitize_text_field(
                $response['status'] ?? 'created'
            )
        );

        $item->save();

        error_log(
            'ML RPX: Transfer Now SUCCESS | Order #' .
            $order_id .
            ' | Item #' .
            $item_id .
            ' | Payout ID=' .
            $response['id'] .
            ' | Amount=₹' .
            $payout_amount
        );

        /*
        * -------------------------------------------------
        * 12. Return result
        * -------------------------------------------------
        */

        return array(
            'success' => true,
            'order_id' => $order_id,
            'item_id' => $item_id,
            'couple_user_id' => $couple_user_id,
            'fund_account_id' => $fund_account_id,
            'amount' => $payout_amount,
            'payout_id' => $response['id'],
            'status' => $response['status'] ?? 'created',
            'response' => $response,
        );
    }
    /**
     * Create payout directly for a WooCommerce order.
     *
     * The couple is identified using:
     * _couple_user_id
     *
     * The payout recipient is identified using:
     * _ml_rpx_fund_account_id
     *
     * Amount comes from the eligible order item's custom_price.
     */
    public static function create_for_order($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error(
                'invalid_order',
                'WooCommerce order not found.'
            );
        }
        /*
        * -------------------------------------------------
        * 1. Get Couple User ID
        * -------------------------------------------------
        *
        * Prefer the Lifafa item's `gift_for`.
        * Fall back to order-level `_couple_user_id`.
        */

        $couple_user_id = 0;

        /*
        * First look for gift_for on an eligible Lifafa item.
        */
        foreach ($order->get_items('line_item') as $item_id => $item) {

            $product = $item->get_product();

            if (!$product) {
                continue;
            }

            $product_id = $product->get_id();

            /*
            * Only cash/Lifafa products.
            */
            if (!has_term('cash', 'product_cat', $product_id)) {
                continue;
            }

            $gift_for = (int) $item->get_meta('gift_for', true);

            if ($gift_for) {

                $couple_user_id = $gift_for;

                error_log(
                    'ML RPX: Order #' . $order_id .
                    ' Couple User ID from gift_for = ' . $couple_user_id .
                    ' Item ID = ' . $item_id
                );

                break;
            }
        }

        /*
        * Fallback to order-level couple ID.
        */
        if (!$couple_user_id) {

            $couple_user_id = (int) $order->get_meta('_couple_user_id');

            error_log(
                'ML RPX: Order #' . $order_id .
                ' Couple User ID from _couple_user_id = ' .
                $couple_user_id
            );
        }

        if (!$couple_user_id) {

            return new WP_Error(
                'couple_user_missing',
                'Couple user ID is not available for this order.'
            );
        }

        /*
         * -------------------------------------------------
         * 2. Get RazorpayX Fund Account
         * -------------------------------------------------
         */

        $fund_account_id = get_user_meta(
            $couple_user_id,
            '_ml_rpx_fund_account_id',
            true
        );

        error_log(
            'ML RPX: Couple #' . $couple_user_id .
            ' Fund Account = ' . $fund_account_id
        );

        if (empty($fund_account_id)) {
            return new WP_Error(
                'fund_account_missing',
                'Couple does not have a RazorpayX fund account.'
            );
        }

        /*
         * -------------------------------------------------
         * 3. Find eligible Lifafa / Cash products
         * -------------------------------------------------
         */

        $payout_amount = 0;
        $payout_items = array();

        foreach ($order->get_items('line_item') as $item_id => $item) {

            $product = $item->get_product();

            if (!$product) {
                continue;
            }

            $product_id = $product->get_id();

            /*
             * Only products belonging to the "cash" category
             * are eligible for couple payout.
             */
            if (!has_term('cash', 'product_cat', $product_id)) {
                continue;
            }

            /*
             * Get custom_price.
             *
             * First check order item meta.
             */
            $custom_price = $item->get_meta('custom_price', true);

            /*
             * If not present on item, check product meta.
             */
            if ($custom_price === '' || $custom_price === null) {
                $custom_price = get_post_meta(
                    $product_id,
                    'custom_price',
                    true
                );
            }

            if ($custom_price === '' || $custom_price === null) {
                error_log(
                    'ML RPX: custom_price not found for product #' .
                    $product_id
                );

                continue;
            }

            /*
             * Remove currency symbols / commas.
             */
            $custom_price = preg_replace(
                '/[^\d.]/',
                '',
                (string) $custom_price
            );

            $custom_price = (float) $custom_price;

            if ($custom_price <= 0) {
                continue;
            }

            /*
             * Respect quantity.
             */
            $quantity = max(
                1,
                (int) $item->get_quantity()
            );

            $item_payout = $custom_price * $quantity;

            $payout_amount += $item_payout;

            $payout_items[] = array(
                'item_id'       => $item_id,
                'product_id'    => $product_id,
                'product_name'  => $product->get_name(),
                'custom_price'  => $custom_price,
                'quantity'      => $quantity,
                'amount'        => $item_payout,
            );

            error_log(
                'ML RPX: Eligible item #' . $item_id .
                ' Product #' . $product_id .
                ' custom_price=' . $custom_price .
                ' quantity=' . $quantity .
                ' payout=' . $item_payout
            );
        }

        /*
         * -------------------------------------------------
         * 4. Validate payout amount
         * -------------------------------------------------
         */

        if ($payout_amount <= 0) {
            return new WP_Error(
                'payout_amount_missing',
                'No eligible Lifafa custom_price was found in this order.'
            );
        }

        error_log(
            'ML RPX: Final payout amount for order #' .
            $order_id . ' = ₹' . $payout_amount
        );

        /*
         * -------------------------------------------------
         * 5. Prevent duplicate payout
         * -------------------------------------------------
         */

        $existing_payout_id = $order->get_meta(
            '_ml_rpx_payout_id'
        );

        if (!empty($existing_payout_id)) {

            return new WP_Error(
                'payout_already_created',
                'A RazorpayX payout already exists for this order: ' .
                $existing_payout_id
            );
        }

        /*
        * -------------------------------------------------
        * Create / reuse RazorpayX idempotency key
        * -------------------------------------------------
        */

        $idempotency_key = $order->get_meta(
            '_ml_rpx_payout_idempotency_key'
        );

        if (empty($idempotency_key)) {

            $idempotency_key = 'ML_' . $order_id . '_' . substr(
                md5(uniqid('', true)),
                0,
                20
            );

            $order->update_meta_data(
                '_ml_rpx_payout_idempotency_key',
                $idempotency_key
            );

            $order->save();
        }

        error_log(
            'ML RPX: Idempotency key for order #' .
            $order_id . ' = ' . $idempotency_key
        );

        /*
         * -------------------------------------------------
         * 6. Create reference ID
         * -------------------------------------------------
         */

        $reference_id = 'ML_ORDER_' . $order_id;

        /*
         * RazorpayX reference_id has length restrictions.
         * Keep it short and unique.
         */
        $reference_id = substr(
            sanitize_text_field($reference_id),
            0,
            40
        );

        /*
         * -------------------------------------------------
         * 7. Create RazorpayX payout
         * -------------------------------------------------
         */

        $data = array(
            'account_number' => ML_RPX_ACCOUNT_NUMBER,
            'fund_account_id' => $fund_account_id,

            /*
             * RazorpayX expects amount in paise.
             */
            'amount' => (int) round(
                $payout_amount * 100
            ),

            'currency' => 'INR',

            'mode' => 'IMPS',

            'purpose' => 'payout',

            'queue_if_low_balance' => true,

            'reference_id' => $reference_id,

            'narration' => 'Modern Lifafa Gift',
        );

        error_log(
            'ML RPX: Sending payout request: ' .
            wp_json_encode(
                array(
                    'order_id' => $order_id,
                    'couple_user_id' => $couple_user_id,
                    'fund_account_id' => $fund_account_id,
                    'amount' => $payout_amount,
                    'amount_paise' => $data['amount'],
                    'reference_id' => $reference_id,
                )
            )
        );

        /*
         * -------------------------------------------------
         * 8. Call RazorpayX
         * -------------------------------------------------
         */

        $response = ML_RPX_API::request(
            'POST',
            '/payouts',
            $data,
            array(
                'X-Payout-Idempotency' => $idempotency_key,
            )
        );

        if (is_wp_error($response)) {

            error_log(
                'ML RPX: Payout API error for order #' .
                $order_id . ': ' .
                $response->get_error_message()
            );

            return $response;
        }

        /*
         * -------------------------------------------------
         * 9. Validate RazorpayX response
         * -------------------------------------------------
         */

        if (empty($response['id'])) {

            error_log(
                'ML RPX: RazorpayX returned invalid response: ' .
                wp_json_encode($response)
            );

            return new WP_Error(
                'payout_failed',
                'RazorpayX payout was not created.'
            );
        }

        /*
         * -------------------------------------------------
         * 10. Save payout information on order
         * -------------------------------------------------
         */

        $order->update_meta_data(
            '_ml_rpx_payout_id',
            sanitize_text_field($response['id'])
        );

        $order->update_meta_data(
            '_ml_rpx_payout_amount',
            $payout_amount
        );

        $order->update_meta_data(
            '_ml_rpx_payout_couple_user_id',
            $couple_user_id
        );

        $order->update_meta_data(
            '_ml_rpx_payout_fund_account_id',
            sanitize_text_field($fund_account_id)
        );

        $order->update_meta_data(
            '_ml_rpx_payout_status',
            sanitize_text_field(
                $response['status'] ?? 'created'
            )
        );

        $order->save();

        error_log(
            'ML RPX: SUCCESS. Order #' .
            $order_id .
            ' payout ID=' .
            $response['id']
        );

        /*
         * -------------------------------------------------
         * 11. Return result
         * -------------------------------------------------
         */

        return array(
            'success' => true,
            'order_id' => $order_id,
            'couple_user_id' => $couple_user_id,
            'fund_account_id' => $fund_account_id,
            'amount' => $payout_amount,
            'payout_id' => $response['id'],
            'status' => $response['status'] ?? 'created',
            'items' => $payout_items,
            'response' => $response,
        );
    }


    /**
     * Existing generic payout method.
     *
     * Can still be used elsewhere.
     */
    public static function create(
        $user_id,
        $amount,
        $reference_id = ''
    ) {

        $amount = (float) $amount;

        if ($amount <= 0) {
            return new WP_Error(
                'invalid_payout_amount',
                'Payout amount must be greater than zero.'
            );
        }

        $fund_account_id = get_user_meta(
            $user_id,
            '_ml_rpx_fund_account_id',
            true
        );

        if (empty($fund_account_id)) {
            return new WP_Error(
                'fund_account_missing',
                'Couple does not have a RazorpayX fund account.'
            );
        }

        /*
         * RazorpayX uses paise.
         */
        $amount_paise = (int) round(
            $amount * 100
        );

        if (empty($reference_id)) {
            $reference_id =
                'ml_payout_' .
                $user_id .
                '_' .
                time();
        }

        $data = array(
            'fund_account_id' => $fund_account_id,

            'amount' => $amount_paise,

            'currency' => 'INR',

            'mode' => 'IMPS',

            'purpose' => 'payout',

            'queue_if_low_balance' => true,

            'reference_id' => sanitize_text_field(
                $reference_id
            ),

            'narration' => 'Modern Lifafa Gift',
        );

        $response = ML_RPX_API::request(
            'POST',
            '/payouts',
            $data
        );

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['id'])) {
            return new WP_Error(
                'payout_failed',
                'RazorpayX payout was not created.'
            );
        }

        return $response;
    }
}