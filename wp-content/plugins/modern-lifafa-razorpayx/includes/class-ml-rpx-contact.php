<?php

defined('ABSPATH') || exit;

class ML_RPX_CONTACT
{
    /**
     * Create RazorpayX contact for a couple.
     */
    public static function create($user_id, $name, $email, $phone = '')
    {
        $existing_contact_id = get_user_meta(
            $user_id,
            'razorpayx_contact_id',
            true
        );

        if (!empty($existing_contact_id)) {
            return array(
                'id' => $existing_contact_id,
                'existing' => true,
            );
        }

        $data = array(
            'name'    => sanitize_text_field($name),
            'email'   => sanitize_email($email),
            'contact' => sanitize_text_field($phone),
            'type'    => 'customer',
            'reference_id' => 'ml_user_' . absint($user_id),
        );

        $response = ML_RPX_API::request(
            'POST',
            '/contacts',
            $data
        );

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['id'])) {
            return new WP_Error(
                'rpx_contact_failed',
                'RazorpayX contact was not created.'
            );
        }

        update_user_meta(
            $user_id,
            'razorpayx_contact_id',
            sanitize_text_field($response['id'])
        );

        return $response;
    }
}