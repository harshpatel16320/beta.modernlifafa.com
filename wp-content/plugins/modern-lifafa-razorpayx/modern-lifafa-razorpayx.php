<?php
/**
 * Plugin Name: Custom RazorpayX Payouts
 * Description: RazorpayX integration for Modern Lifafa couple payouts.
 * Version: 1.0.0
 * Author: Cmarix
 */

defined('ABSPATH') || exit;

define('ML_RPX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ML_RPX_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ML_RPX_PLUGIN_DIR . 'includes/class-ml-rpx-api.php';
require_once ML_RPX_PLUGIN_DIR . 'includes/class-ml-rpx-contact.php';
require_once ML_RPX_PLUGIN_DIR . 'includes/class-ml-rpx-fund-account.php';
require_once ML_RPX_PLUGIN_DIR . 'includes/class-ml-rpx-payout.php';
require_once ML_RPX_PLUGIN_DIR . 'admin/class-ml-rpx-admin.php';

/**
 * Process RazorpayX payout after WooCommerce payment is completed.
 */
/*
add_action(
    'woocommerce_payment_complete',
    'ml_rpx_process_order_payout',
    20
);

function ml_rpx_process_order_payout($order_id)
{
    error_log(
        'ML RPX: Payment complete hook fired for order #' . $order_id
    );

    $result = ML_RPX_PAYOUT::create_for_order($order_id);

    if (is_wp_error($result)) {

        error_log(
            'ML RPX: Payout failed for order #' .
            $order_id .
            ': ' .
            $result->get_error_message()
        );

        return;
    }

    error_log(
        'ML RPX: Payout successfully created for order #' .
        $order_id .
        ' | Payout ID: ' .
        ($result['payout_id'] ?? 'unknown') .
        ' | Amount: ₹' .
        ($result['amount'] ?? '0')
    );
}
*/

/**
 * RazorpayX - Save Bank Account
 */
add_action('wp_ajax_ml_rpx_save_bank_account', 'ml_rpx_save_bank_account');

function ml_rpx_save_bank_account() {

    // -------------------------------------------------
    // 1. Check login
    // -------------------------------------------------
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'You must be logged in.'
        ], 401);
    }

    // -------------------------------------------------
    // 2. Verify nonce
    // -------------------------------------------------
    check_ajax_referer(
        'ml_rpx_save_bank_account',
        'nonce'
    );

    $user_id = get_current_user_id();

    // -------------------------------------------------
    // 3. Get submitted bank details
    // -------------------------------------------------
    $account_holder_name = isset($_POST['account_holder_name'])
        ? sanitize_text_field(wp_unslash($_POST['account_holder_name']))
        : '';

    $account_number = isset($_POST['account_number'])
        ? preg_replace('/\s+/', '', wp_unslash($_POST['account_number']))
        : '';

    $bank_name = isset($_POST['bank_name'])
        ? sanitize_text_field(wp_unslash($_POST['bank_name']))
        : '';

    $bank_ifsc = isset($_POST['bank_ifsc'])
        ? strtoupper(sanitize_text_field(wp_unslash($_POST['bank_ifsc'])))
        : '';

    $bank_city = isset($_POST['bank_city'])
        ? sanitize_text_field(wp_unslash($_POST['bank_city']))
        : '';

    // -------------------------------------------------
    // 4. Validate required fields
    // -------------------------------------------------
    if (!$account_holder_name) {
        wp_send_json_error([
            'message' => 'Account holder name is required.'
        ]);
    }

    if (!$account_number) {
        wp_send_json_error([
            'message' => 'Account number is required.'
        ]);
    }

    if (!$bank_ifsc) {
        wp_send_json_error([
            'message' => 'IFSC is required.'
        ]);
    }

    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $bank_ifsc)) {
        wp_send_json_error([
            'message' => 'Invalid IFSC code.'
        ]);
    }

    // -------------------------------------------------
    // 5. RazorpayX credentials
    // -------------------------------------------------
    if (!defined('ML_RPX_KEY_ID') || !defined('ML_RPX_KEY_SECRET')) {
        wp_send_json_error([
            'message' => 'RazorpayX credentials are not configured.'
        ], 500);
    }

    $key_id     = ML_RPX_KEY_ID;
    $key_secret = ML_RPX_KEY_SECRET;

    // -------------------------------------------------
    // Helper for RazorpayX API
    // -------------------------------------------------
    $rpx_request = function ($endpoint, $method = 'POST', $body = null) use (
        $key_id,
        $key_secret
    ) {

        $args = [
            'method'  => $method,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(
                    $key_id . ':' . $key_secret
                ),
                'Content-Type' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request(
            'https://api.razorpay.com/v1/' . ltrim($endpoint, '/'),
            $args
        );

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error'   => $response->get_error_message(),
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($status_code < 200 || $status_code >= 300) {
            return [
                'success' => false,
                'status'  => $status_code,
                'data'    => $data,
                'raw'     => $response_body,
            ];
        }

        return [
            'success' => true,
            'status'  => $status_code,
            'data'    => $data,
        ];
    };

    // -------------------------------------------------
    // 6. Get existing RazorpayX Contact
    // -------------------------------------------------
    $contact_id = get_user_meta(
        $user_id,
        '_ml_rpx_contact_id',
        true
    );

    // -------------------------------------------------
    // 7. Create RazorpayX Contact if required
    // -------------------------------------------------
    if (empty($contact_id)) {

        $user = get_userdata($user_id);

        if (!$user) {
            wp_send_json_error([
                'message' => 'Unable to load user.'
            ], 500);
        }

        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name  = get_user_meta($user_id, 'last_name', true);

        $name = trim(
            $first_name . ' ' . $last_name
        );

        if (!$name) {
            $name = $account_holder_name;
        }

        $contact_payload = [
            'name'    => $name,
            'email'   => $user->user_email,
            'contact' => get_user_meta($user_id, 'phone', true),
            'type'    => 'customer',
            'reference_id' => 'ML_USER_' . $user_id,
        ];

        $contact_result = $rpx_request(
            'contacts',
            'POST',
            $contact_payload
        );

        if (!$contact_result['success']) {

            error_log(
                'RazorpayX Contact Error: ' .
                wp_json_encode($contact_result)
            );

            wp_send_json_error([
                'message' => 'Unable to create RazorpayX contact.'
            ], 500);
        }

        $contact_id = $contact_result['data']['id'] ?? '';

        if (!$contact_id) {
            wp_send_json_error([
                'message' => 'RazorpayX did not return a contact ID.'
            ], 500);
        }

        // Store ONLY the RazorpayX contact ID
        update_user_meta(
            $user_id,
            '_ml_rpx_contact_id',
            sanitize_text_field($contact_id)
        );
    }

    // -------------------------------------------------
    // 8. Create RazorpayX Fund Account
    // -------------------------------------------------
    $fund_account_payload = [
        'contact_id' => $contact_id,

        'account_type' => 'bank_account',

        'bank_account' => [
            'name'           => $account_holder_name,
            'ifsc'           => $bank_ifsc,
            'account_number' => $account_number,
        ],
    ];

    $fund_account_result = $rpx_request(
        'fund_accounts',
        'POST',
        $fund_account_payload
    );

    if (!$fund_account_result['success']) {

        error_log(
            'RazorpayX Fund Account Error: ' .
            wp_json_encode($fund_account_result)
        );

        wp_send_json_error([
            'message' => 'Unable to connect your bank account with RazorpayX.'
        ], 500);
    }

    $fund_account_id = $fund_account_result['data']['id'] ?? '';

    if (!$fund_account_id) {
        wp_send_json_error([
            'message' => 'RazorpayX did not return a Fund Account ID.'
        ], 500);
    }

    // -------------------------------------------------
    // 9. Store ONLY RazorpayX IDs
    // -------------------------------------------------

    update_user_meta(
        $user_id,
        '_ml_rpx_fund_account_id',
        sanitize_text_field($fund_account_id)
    );

    update_user_meta(
        $user_id,
        '_ml_rpx_bank_status',
        'connected'
    );

    update_user_meta(
        $user_id,
        '_ml_rpx_bank_city',
        $bank_city
    );

    // -------------------------------------------------
    // 10. Success
    // -------------------------------------------------
    wp_send_json_success([
        'message' => 'Your bank account has been securely connected.',
        'fund_account_id' => $fund_account_id,
    ]);
}

add_action(
    'wp_ajax_ml_rpx_update_bank_account',
    'ml_rpx_update_bank_account'
);

function ml_rpx_update_bank_account()
{
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'You must be logged in.'
        ], 401);
    }

    check_ajax_referer(
        'ml_rpx_update_bank_account',
        'nonce'
    );

    $user_id = get_current_user_id();

    $account_holder_name = sanitize_text_field(
        wp_unslash($_POST['account_holder_name'] ?? '')
    );

    $account_number = sanitize_text_field(
        wp_unslash($_POST['account_number'] ?? '')
    );

    $bank_name = sanitize_text_field(
        wp_unslash($_POST['bank_name'] ?? '')
    );

    $bank_ifsc = strtoupper(
        sanitize_text_field(
            wp_unslash($_POST['bank_ifsc'] ?? '')
        )
    );

    $bank_city = sanitize_text_field(
        wp_unslash($_POST['bank_city'] ?? '')
    );


    if (
        empty($account_holder_name) ||
        empty($account_number) ||
        empty($bank_ifsc)
    ) {
        wp_send_json_error([
            'message' => 'Please enter all required bank details.'
        ]);
    }


    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $bank_ifsc)) {
        wp_send_json_error([
            'message' => 'Invalid IFSC code.'
        ]);
    }


    /*
     * Get existing RazorpayX Fund Account
     */
    $old_fund_account_id = get_user_meta(
        $user_id,
        '_ml_rpx_fund_account_id',
        true
    );


    /*
     * Get RazorpayX Contact ID
     */
    $contact_id = get_user_meta(
        $user_id,
        '_ml_rpx_contact_id',
        true
    );


    if (empty($contact_id)) {
        wp_send_json_error([
            'message' => 'RazorpayX contact not found.'
        ]);
    }


    /*
     * RazorpayX credentials
     */
    $key_id = defined('ML_RPX_KEY_ID')
        ? ML_RPX_KEY_ID
        : '';

    $key_secret = defined('ML_RPX_KEY_SECRET')
        ? ML_RPX_KEY_SECRET
        : '';


    if (empty($key_id) || empty($key_secret)) {

        wp_send_json_error([
            'message' => 'RazorpayX configuration is missing.'
        ]);
    }


    /*
     * Create NEW Fund Account
     */
    $response = wp_remote_post(
        'https://api.razorpay.com/v1/fund_accounts',
        [
            'timeout' => 30,

            'headers' => [
                'Authorization' =>
                    'Basic ' . base64_encode(
                        $key_id . ':' . $key_secret
                    ),

                'Content-Type' => 'application/json',
            ],

            'body' => wp_json_encode([
                'contact_id' => $contact_id,

                'account_type' => 'bank_account',

                'bank_account' => [
                    'name' => $account_holder_name,
                    'ifsc' => $bank_ifsc,
                    'account_number' => $account_number,
                ],
            ]),
        ]
    );


    if (is_wp_error($response)) {

        wp_send_json_error([
            'message' => $response->get_error_message()
        ]);
    }


    $status_code = wp_remote_retrieve_response_code($response);

    $body = json_decode(
        wp_remote_retrieve_body($response),
        true
    );


    if (
        $status_code < 200 ||
        $status_code >= 300 ||
        empty($body['id'])
    ) {

        wp_send_json_error([
            'message' =>
                $body['error']['description']
                ?? 'Unable to create new RazorpayX fund account.'
        ]);
    }


    /*
     * New Fund Account ID
     */
    $new_fund_account_id = sanitize_text_field(
        $body['id']
    );


    /*
     * Deactivate old Fund Account
     */
    if (
        !empty($old_fund_account_id) &&
        $old_fund_account_id !== $new_fund_account_id
    ) {

        wp_remote_request(
            'https://api.razorpay.com/v1/fund_accounts/' .
            rawurlencode($old_fund_account_id),

            [
                'method' => 'PATCH',

                'timeout' => 30,

                'headers' => [
                    'Authorization' =>
                        'Basic ' . base64_encode(
                            $key_id . ':' . $key_secret
                        ),

                    'Content-Type' => 'application/json',
                ],

                'body' => wp_json_encode([
                    'active' => false
                ]),
            ]
        );
    }


    /*
     * Store ONLY the new Fund Account ID
     */
    update_user_meta(
        $user_id,
        '_ml_rpx_fund_account_id',
        $new_fund_account_id
    );


    update_user_meta(
        $user_id,
        '_ml_rpx_bank_status',
        'connected'
    );

    update_user_meta(
        $user_id,
        '_ml_rpx_bank_city',
        $bank_city
    );


    wp_send_json_success([
        'message' => 'Your new bank account has been connected successfully.'
    ]);
}

add_action(
    'wp_ajax_ml_rpx_get_bank_account',
    'ml_rpx_get_bank_account'
);
function ml_rpx_get_bank_account()
{
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'You must be logged in.'
        ], 401);
    }

    check_ajax_referer(
        'ml_rpx_get_bank_account',
        'nonce'
    );

    $user_id = get_current_user_id();

    $bank_city = get_user_meta(
        $user_id,
        '_ml_rpx_bank_city',
        true
    );

    /*
     * Get stored RazorpayX Fund Account ID
     */
    $fund_account_id = get_user_meta(
        $user_id,
        '_ml_rpx_fund_account_id',
        true
    );

    if (empty($fund_account_id)) {
        wp_send_json_error([
            'message' => 'No RazorpayX bank account is connected.'
        ], 404);
    }

    /*
     * RazorpayX credentials
     */
    $key_id = defined('ML_RPX_KEY_ID')
        ? ML_RPX_KEY_ID
        : '';

    $key_secret = defined('ML_RPX_KEY_SECRET')
        ? ML_RPX_KEY_SECRET
        : '';

    if (empty($key_id) || empty($key_secret)) {
        wp_send_json_error([
            'message' => 'RazorpayX configuration is missing.'
        ], 500);
    }

    /*
     * Get Fund Account from RazorpayX
     */
    $response = wp_remote_get(
        'https://api.razorpay.com/v1/fund_accounts/' .
        rawurlencode($fund_account_id),
        [
            'timeout' => 30,

            'headers' => [
                'Authorization' =>
                    'Basic ' . base64_encode(
                        $key_id . ':' . $key_secret
                    ),

                'Content-Type' => 'application/json',
            ],
        ]
    );

    /*
     * WordPress HTTP error
     */
    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => $response->get_error_message()
        ], 500);
    }

    $status_code = wp_remote_retrieve_response_code($response);

    $body = json_decode(
        wp_remote_retrieve_body($response),
        true
    );

    /*
     * RazorpayX API error
     */
    if (
        $status_code < 200 ||
        $status_code >= 300
    ) {
        wp_send_json_error([
            'message' =>
                $body['error']['description']
                ?? 'Unable to retrieve bank details from RazorpayX.',
            'razorpay_response' => $body
        ], $status_code);
    }

    /*
     * Make sure bank account exists
     */
    if (
        empty($body['bank_account']) ||
        empty($body['id'])
    ) {
        wp_send_json_error([
            'message' => 'Bank account details were not found in RazorpayX.'
        ], 404);
    }

    $bank = $body['bank_account'];

    /*
     * Return bank details to frontend
     *
     * IMPORTANT:
     * These values come directly from RazorpayX.
     * They are NOT read from WordPress user meta.
     */
    wp_send_json_success([
        'bank' => [
            'name' => $bank['name'] ?? '',
            'account_number' => $bank['account_number'] ?? '',
            'bank_name' => $bank['bank_name'] ?? '',
            'ifsc' => $bank['ifsc'] ?? '',
            'city' => $bank_city,
        ]
    ]);
}

