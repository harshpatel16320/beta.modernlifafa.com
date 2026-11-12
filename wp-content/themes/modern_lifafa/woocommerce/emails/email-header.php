<?php
/**
 * Email Header
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-header.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 7.4.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
<html lang='en'>
  <head>
      <link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
      <meta charset='UTF-8' />
      <meta http-equiv='X-UA-Compatible' content='IE=edge' />
      <meta name='viewport' content='width=device-width, initial-scale=1.0' />
      <title>Email Template</title>
  </head>
  <body style='background-color: #fff;margin: 0;'>
      <table style='width: 100%;border-collapse: collapse;max-width: 650px; margin: 0 auto; border: 1px solid #DD1265;' cellspacing='0' cellpadding='0' width='100%' class="email_template1">
          <tbody style="background-color: #fff;" class="email_template_">
              <tr style="background-color: #fff;">
                  <td align='center'>
                      <picture><img src="<?php echo home_url(); ?>/wp-content/uploads/2025/05/full-header.png" style="margin: 0; width: 100%;" alt="Full header" /></picture>
                  </td>
              </tr>