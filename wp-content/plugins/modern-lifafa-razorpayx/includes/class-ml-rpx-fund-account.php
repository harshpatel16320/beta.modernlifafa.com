<?php

defined('ABSPATH') || exit;

class ML_RPX_FUND_ACCOUNT
{
    /**
     * Create or replace RazorpayX fund account.
     */
    public static function create(
        $user_id,
        $account_holder_name,
        $account_number,
        $ifsc
    ) {

        $account_holder_name = sanitize_text_field($account_holder_name);
        $account_number      = preg_replace('/\D+/', '', $account_number);
        $ifsc                = strtoupper(
            sanitize_text_field($ifsc)
        );

        if (empty($account_holder_name)) {
            return new WP_Error(
                'invalid_account_name',
                'Account holder name is required.'
            );
        }

        if (empty($account_number)) {
            return new WP_Error(
                'invalid_account_number',
                'Account number is required.'
            );
        }

        if (empty($ifsc)) {
            return new WP_Error(
                'invalid_ifsc',
                'IFSC is required.'
            );
        }

        /*
         * Get existing RazorpayX contact.
         */
        $contact_id = get_user_meta(
            $user_id,
            'razorpayx_contact_id',
            true
        );

        if (empty($contact_id)) {

            $user = get_userdata($user_id);

            if (!$user) {
                return new WP_Error(
                    'user_not_found',
                    'User not found.'
                );
            }

            $name = trim(
                get_user_meta(
                    $user_id,
                    'first_name',
                    true
                ) . ' ' .
                get_user_meta(
                    $user_id,
                    'last_name',
                    true
                )
            );

            if (empty($name)) {
                $name = $user->display_name;
            }

            $phone = get_user_meta(
                $user_id,
                'phone',
                true
            );

            $contact = ML_RPX_CONTACT::create(
                $user_id,
                $name,
                $user->user_email,
                $phone
            );

            if (is_wp_error($contact)) {
                return $contact;
            }

            $contact_id = $contact['id'];
        }

        /*
         * Create fund account in RazorpayX.
         */
        $data = array(
            'contact_id'  => $contact_id,
            'account_type' => 'bank_account',
            'bank_account' => array(
                'name'           => $account_holder_name,
                'account_number' => $account_number,
                'ifsc'           => $ifsc,
            ),
        );

        $response = ML_RPX_API::request(
            'POST',
            '/fund_accounts',
            $data
        );

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['id'])) {
            return new WP_Error(
                'fund_account_failed',
                'RazorpayX fund account was not created.'
            );
        }

        /*
         * Store ONLY the RazorpayX reference.
         */
        update_user_meta(
            $user_id,
            'razorpayx_fund_account_id',
            sanitize_text_field($response['id'])
        );

        /*
         * Store masked account number for display.
         * This is NOT the raw bank number.
         */
        $last_four = substr($account_number, -4);

        update_user_meta(
            $user_id,
            'razorpayx_bank_last4',
            sanitize_text_field($last_four)
        );

        /*
         * Remove legacy raw bank data if it exists.
         */
        delete_user_meta(
            $user_id,
            'account_number'
        );

        delete_user_meta(
            $user_id,
            'account_holder_name'
        );

        delete_user_meta(
            $user_id,
            'bank_name'
        );

        delete_user_meta(
            $user_id,
            'bank_ifsc'
        );

        delete_user_meta(
            $user_id,
            'bank_city'
        );

        return $response;
    }
}