<?php

class Zoho_API {

    // Refresh Zoho Access Token
    
   
    public static function refresh_zoho_access_token() {
        $client_id = '1000.GW2PTOJ7UX50XNKWEC2PTV0IP4GJJH';
        $client_secret = '1f3165e51c8afc8c1d5d5daa56f430e8a5d2007758';
        $refresh_token = get_option('zoho_refresh_token');
        if (!$refresh_token) {
            error_log('Zoho Refresh Token is missing. Please configure it in the WordPress options.');
            return false;
        }
        $response = wp_remote_post('https://accounts.zoho.in/oauth/v2/token', [
            'body' => [
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'refresh_token',
            ],
        ]);
        if (is_wp_error($response)) {
            error_log('Zoho Token Refresh Error: ' . $response->get_error_message());
            return false;
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['access_token'])) {
            update_option('zoho_access_token', $body['access_token']);
            return $body['access_token'];
        }
        error_log('Failed to refresh Zoho access token: ' . print_r($body, true));
        return false;
    }
    // $access_token = refresh_zoho_access_token();
    // Send data to Zoho
    public static function send_data_to_zoho($data) {
        $access_token = get_option('zoho_access_token');
        if (!$access_token) {
            $access_token = self::refresh_zoho_access_token();
            if (!$access_token) {
                error_log('Zoho API Error: Unable to refresh access token.');
                return;
            }
        }
        // var_dump($access_token);
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // var_dump($access_token);
        $module_name = 'ModernLifafaOrderdetail';
        $order_id = $data['OrderId'];

        $search_url = "https://www.zohoapis.in/crm/v3/$module_name/search?criteria=(OrderId:equals:$order_id)";

        $search_response = wp_remote_get($search_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (is_wp_error($search_response)) {
            error_log('Zoho API Error (Search): ' . $search_response->get_error_message());
            return;
        }

        $search_body = json_decode(wp_remote_retrieve_body($search_response), true);

        // $response = wp_remote_post($url, [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $access_token,
        //         'Content-Type' => 'application/json',
        //     ],
        //     'body' => json_encode([
        //         'data' => [$data],
        //     ]),
        // ]);
        
        if (!empty($search_body['data'])) {
            $record_id = $search_body['data'][0]['id']; 
            $update_url = "https://www.zohoapis.in/crm/v3/$module_name";
            $update_response = wp_remote_request($update_url, [
                'method' => 'PUT',
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'data' => [[ 'id' => $record_id ] + $data ],
                ]),
            ]);
            if (is_wp_error($update_response)) {
                error_log('Zoho API Error (Update): ' . $update_response->get_error_message());
            } else {
                error_log('Order updated successfully in Zoho: ' . wp_remote_retrieve_body($update_response));
            }
        } else {
            $create_url = "https://www.zohoapis.in/crm/v3/$module_name";
            $create_response = wp_remote_post($create_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(['data' => [$data]]),
            ]);
    
            if (is_wp_error($create_response)) {
                error_log('Zoho API Error (Create): ' . $create_response->get_error_message());
            } else {
                error_log('Order created successfully in Zoho: ' . wp_remote_retrieve_body($create_response));
            }
        }

        // if (is_wp_error($response)) {
        //     error_log('Zoho API Error: ' . $response->get_error_message());
        // } else {
        //     $body = json_decode(wp_remote_retrieve_body($response), true);
        //     if (isset($body['code']) && $body['code'] === 'INVALID_TOKEN') {
        //         $access_token = self::refresh_zoho_access_token();
        //         if ($access_token) {
        //             self::send_data_to_zoho($data);
        //         }
        //     } else {
        //         error_log('Data sent to Zoho successfully: ' . wp_remote_retrieve_body($response));
        //     }
        // }
    }
}
