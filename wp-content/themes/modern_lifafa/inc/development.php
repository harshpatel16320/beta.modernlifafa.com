<?php

function send_email_on_order_complete($order_id)
{
    if (!$order_id) {
        error_log('Order ID missing.');
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("Order #{$order_id} not found.");
        return;
    }

    $cash_items = [];
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if (!$product) {
            error_log("Item ID {$item_id} has no product.");
            continue;
        }

        $product_id = $product->get_id();
        $product_name = $product->get_name();

        if (has_term('cash', 'product_cat', $product_id)) {
            error_log("Product '{$product_name}' (ID: {$product_id}) is in 'cash' category.");
            $cash_items[] = $item;
        } else {
            error_log("Product '{$product_name}' (ID: {$product_id}) is NOT in 'cash' category.");
        }
    }


    // if (!empty($cash_items)) {
    //     send_cash_product_email($order, $cash_items);
    // }

    error_log("Processing email for Order #{$order_id}");

    $deliver_to_couple = $order->get_meta('deliver_to_couple');
    error_log("Deliver to couple: {$deliver_to_couple}");

    $gift_for = '';
    foreach ($order->get_items() as $item) {
        $gift_for = $item->get_meta('gift_for');
        if ($gift_for)
            break;
    }

    if (!$gift_for) {
        error_log("No 'gift_for' metadata found in order items.");
        return;
    }

    $user_info = get_userdata($gift_for);
    if (!$user_info) {
        error_log("User not found for ID: {$gift_for}");
        return;
    }

    $user_email = $user_info->user_email;
    $couple_name = get_user_meta($user_info->ID, 'first_name', true) . ' & ' . get_user_meta($user_info->ID, 'partner_fname', true);
    $billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    $image12 = '';
    $img = get_option('woocommerce_email_header_image');
    if ($img) {
        $image12 = '<picture style="transform: translateY(40px);"><img style="width: auto; height: 90px; vertical-align: middle; transform: translateY(40px); margin-top: 15px;" src="' . esc_url($img) . '" alt="' . esc_attr(get_bloginfo('name', 'display')) . '" /></picture>';
    }

    $home_url = home_url();
    $year = date('Y');

    $email_footer = "
        <tr style='background-color: #DD1265;'>
            <td style='padding: 14px 20px; background: #ffc4d8;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='max-width: 650px; width: 100%;'>
                    <tr>
                        <td style='display: block; width: 100%; text-align: center; padding-bottom: 10px; font-family: Segoe UI, Helvetica, sans-serif; font-size: 14px; color: #5c164c; line-height: 20px;'>
                            <a href='{$home_url}/privacy-policy/' style='color: #5c164c; text-decoration: none; margin-right: 8px;'>Privacy Policy</a> |
                            <a href='{$home_url}/contact-us/' style='color: #5c164c; text-decoration: none; margin: 0 8px;'>Contact Us</a> |
                            <a href='{$home_url}' style='color: #5c164c; text-decoration: none; margin: 0 8px;'>Find a Couple</a> |
                            <a href='{$home_url}/sign-up/' style='color: #5c164c; text-decoration: none; margin-left: 8px;'>Create a Registry</a>
                        </td>
                    </tr>
                    <tr>
                        <td style='text-align: center;'>
                            <a href='https://www.instagram.com/accounts/login/?next=%2Fmodernlifafa%2F&source=omni_redirect' target='_blank'><img src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/instagram.png' alt='Instagram' width='28' height='28' style='margin: 5px;'></a>
                            <a href='https://www.facebook.com/people/Modern-Lifafa/61573859936910/?mibextid=wwXIfr&rdid=COOnmMhuMwzyWZcG&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1XfR4u8pcd%2F%3Fmibextid%3DwwXIfr' target='_blank'><img src='{$home_url}/wp-content/themes/modern_lifafa/assets/images/facebook.png' alt='Facebook' width='28' height='28' style='margin: 5px;'></a>
                        </td>
                    </tr>
                    <tr>
                        <td style='text-align: center; padding-top: 5px;'>
                            <p style='margin: 0; font-size: 14px; color: #5c164c;'>Modern Lifafa ©{$year}, All Rights Reserved</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    ";

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    if (trim(strtolower($deliver_to_couple)) === 'no' && empty($cash_items)) {
        $to = $user_email;
        $subject = 'Ding Ding Ding! You have received a new gift!';
        $message_body = "
        <html><body>
        <table style='width: 100%; border-collapse: collapse; max-width: 650px; margin: 0 auto; border: 1px solid #DD1265; font-family: Segoe UI,Helvetica,sans-serif;'>
           <tr>
                <td align='center' style='display: contents;'>
                    <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png' style='margin: 0; width: 100%;' alt='Full header' /></picture>
                </td>
            </tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Hi <strong style='color:#DD1265;'>{$couple_name}</strong>,</td></tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Exciting news — <strong style='color:#DD1265;'>{$billing_name}</strong>, just picked a gift from your wishlist 💝</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>They’ve chosen to have the gift shipped to their own address so they can give it to you personally.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Log in to your profile <a href='{$home_url}/my-account' target='_blank'>here</a> to see which gift was purchased, along with any personal message they may have left for you.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>We’re so thrilled to be a part of your celebration!</td></tr>
            <tr><td style='padding: 5px 20px 30px; font-size: 16px;'>– The Modern Lifafa Team</td></tr>
            {$email_footer}
        </table>
        </body></html>";

        error_log("Sending email to couple: {$to}");

        // Ship to couple
    } else if (trim(strtolower($deliver_to_couple)) === 'yes' && empty($cash_items)) {
        $to = $user_email;
        $subject = 'Ding Ding Ding! You have received a new gift!';
        $message_body = "
        <html><body>
        <table style='width: 100%; border-collapse: collapse; max-width: 650px; margin: 0 auto; border: 1px solid #DD1265; font-family: Segoe UI,Helvetica,sans-serif;'>
            <tr>
                <td align='center' style='display: contents;'>
                    <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png' style='margin: 0; width: 100%;' alt='Full header' /></picture>
                </td>
            </tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Hi <strong style='color:#DD1265;'>{$couple_name}</strong>,</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Exciting news — <strong style='color:#DD1265;'>{$billing_name}</strong>, just picked a gift from your wishlist 💝</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>The best part? You’re in control! You can ship it to your address of choice now or hold onto it until the timing works better for you.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Log in to your profile <a href='{$home_url}/my-account' target='_blank'>here</a> to see which gift was purchased, along with any personal message they may have left for you.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>We’re so thrilled to be a part of your celebration!</td></tr>
            <tr><td style='padding: 5px 20px 30px; font-size: 16px;'>– The Modern Lifafa Team</td></tr>
            {$email_footer}
        </table>
        </body></html>";


        error_log("Sending email to couple: {$to}");
    }
    if (empty($cash_items)) {
        $sent = wp_mail($to, $subject, $message_body, $headers);

        $to_guest = $order->get_billing_email();
        $subject_guest = 'Thanks for your order!';
        $message_body_guest = "
        <html><body>
        <table style='width: 100%; border-collapse: collapse; max-width: 650px; margin: 0 auto; border: 1px solid #DD1265; font-family: Segoe UI,Helvetica,sans-serif;'>
            <tr>
            <td align='center' style='display: contents;'>
                    <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png' style='margin: 0; width: 100%;' alt='Full header' /></picture>
                </td>
            </tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Hi <strong style='color:#DD1265;'>{$billing_name}</strong>,</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Thanks for picking a gift for <strong style='color:#DD1265;'>{$couple_name}</strong> through Modern Lifafa! ✨</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Your order is confirmed and will be shipped directly to the couple’s address. The couple has also been notified of your generous purchase and gift message!</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>If you have any questions, contact us at info@modernlifafa.com</td></tr>
            <tr><td style='padding: 5px 20px 30px; font-size: 16px;'>Thanks again for making their celebration extra special!<br><br>The Modern Lifafa Team</td></tr>
            {$email_footer}
        </table>
        </body></html>";
        $sent = wp_mail($to_guest, $subject_guest, $message_body_guest, $headers);
    }
    if (!empty($cash_items)) {
        $to_guest = $order->get_billing_email();
        $subject_guest = "Thank you for contributing to " . $couple_name . "'s Cash Fund";
        $message_body_guest = "
        <html><body>
        <table style='width: 100%; border-collapse: collapse; max-width: 650px; margin: 0 auto; border: 1px solid #DD1265; font-family: Segoe UI,Helvetica,sans-serif;'>
            <tr>
                <td align='center' style='display: contents;'>
                    <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png' style='margin: 0; width: 100%;' alt='Full header' /></picture>
                </td>
            </tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Hi <strong style='color:#DD1265;'>{$billing_name}</strong>,</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Thank you for your thoughtful contribution to <strong style='color:#DD1265;'>{$couple_name}'s</strong> Cash Fund on Modern Lifafa! 🎉</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>The couple has been notified and will be able to see your gift (and your personal message, if you added one!).</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Thanks for making their celebration even more special with your generosity!</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>If you have any questions, feel free to reach out to us at [info@modernlifafa.com]</td></tr>
            <tr><td style='padding: 5px 20px 30px; font-size: 16px;'> – The Modern Lifafa Team</td></tr>
            {$email_footer}
        </table>
        </body></html>";
        $sent = wp_mail($to_guest, $subject_guest, $message_body_guest, $headers);

        $to_guest = $user_email;
        $subject_guest = "You've received a Cash Fund contribution!";
        $message_body_guest = "
        <html><body>
        <table style='width: 100%; border-collapse: collapse; max-width: 650px; margin: 0 auto; border: 1px solid #DD1265; font-family: Segoe UI,Helvetica,sans-serif;'>
            <tr>
                <td align='center' style='display: contents;'>
                    <picture><img src='{$home_url}/wp-content/uploads/2025/05/full-header.png' style='margin: 0; width: 100%;' alt='Full header' /></picture>
                </td>
            </tr>
            <tr><td style='padding: 32px 20px 5px; font-size: 16px;'>Hi <strong style='color:#DD1265;'>{$couple_name}</strong>,</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Exciting news — <strong style='color:#DD1265;'>{$billing_name}'s</strong> just contributed to one of your Cash Funds on Modern Lifafa! 🎉</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>You can log in to your profile to view the details and the message they left for you (if they added one!).</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>Your cash gifts will be available for you to transfer whenever you're ready.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'><a href='{$home_url}/my-account' target='_blank'>login </a> view your gifts.</td></tr>
            <tr><td style='padding: 5px 20px; font-size: 16px;'>If you have any questions, we're always here at [info@modernlifafa.com].</td></tr>
            <tr><td style='padding: 5px 20px 30px; font-size: 16px;'> – The Modern Lifafa Team</td></tr>
            {$email_footer}
        </table>
        </body></html>";
        $sent = wp_mail($to_guest, $subject_guest, $message_body_guest, $headers);
    }
    error_log("Sending email to buyer: {$to}");
    error_log("Email sent to {$to}: " . ($sent ? 'SUCCESS' : 'FAILURE'));
}

if (is_user_logged_in() && isset($_GET['original_user_id']) && ($_GET['original_user_id'] != get_current_user_id())) { ?>
    <style>
        .upload-file {
            display: none;
        }

        #share-wishlist {
            display: none;
        }
    </style>
    <?php
}

?>