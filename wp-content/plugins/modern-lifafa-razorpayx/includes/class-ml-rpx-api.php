<?php

defined('ABSPATH') || exit;

class ML_RPX_API
{
    /**
     * Make authenticated RazorpayX API request.
     */
    public static function request(
        $method,
        $endpoint,
        $data = array(),
        $headers = array()
    )
    {
        if (!defined('ML_RPX_KEY_ID') || !defined('ML_RPX_KEY_SECRET')) {
            return new WP_Error(
                'rpx_credentials_missing',
                'RazorpayX API credentials are not configured.'
            );
        }

        $url = rtrim(ML_RPX_API_BASE, '/') . '/' . ltrim($endpoint, '/');

        $args = array(
            'method'  => strtoupper($method),
            'timeout' => 30,
            'headers' => array_merge(
                array(
                    'Authorization' => 'Basic ' . base64_encode(
                        ML_RPX_KEY_ID . ':' . ML_RPX_KEY_SECRET
                    ),
                    'Content-Type' => 'application/json',
                ),
                $headers
            ),
        );

        if (!empty($data)) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log(
                'Modern Lifafa RazorpayX API error: ' .
                $response->get_error_message()
            );

            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $decoded     = json_decode($body, true);

        if ($status_code < 200 || $status_code >= 300) {

            error_log(
                'Modern Lifafa RazorpayX API HTTP ' .
                $status_code .
                ': ' .
                $body
            );

            $message = 'RazorpayX API request failed.';

            if (
                is_array($decoded) &&
                isset($decoded['error']['description'])
            ) {
                $message = $decoded['error']['description'];
            }

            return new WP_Error(
                'rpx_api_error',
                $message,
                array(
                    'status' => $status_code,
                    'response' => $decoded,
                )
            );
        }

        return is_array($decoded) ? $decoded : array();
    }
}