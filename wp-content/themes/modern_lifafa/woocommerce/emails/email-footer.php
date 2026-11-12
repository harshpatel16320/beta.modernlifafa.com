<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 7.4.0
 */

defined('ABSPATH') || exit;
?>
</tbody>
<?php
$fb = get_template_directory_uri() . '/assets/images/facebook.png';
$insta = get_template_directory_uri() . '/assets/images/instagram.png'; ?>
<tr style="background-color: #DD1265;">
  <td style="padding: 14px 20px; background: #ffc4d8;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 650px; width: 100%;">
      <tr>
        <td>
          <!-- Inner Table to Control Layout -->
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
              <!-- Links Column -->
              <td style="display: block; width: 100%; text-align: center; padding-bottom: 10px; font-family: Segoe UI, Helvetica, sans-serif; font-size: 14px; color: #5c164c; line-height: 20px;">
                <a href="<?php echo home_url('/privacy-policy/'); ?>" style="color: #5c164c; text-decoration: none; margin-right: 8px;">Privacy Policy</a> |
                <a href="<?php echo home_url('/contact-us/'); ?>" style="color: #5c164c; text-decoration: none; margin: 0 8px;">Contact Us</a> |
                <a href="<?php echo home_url(); ?>" style="color: #5c164c; text-decoration: none; margin: 0 8px;">Find a Couple</a> |
                <a href="<?php echo home_url('/sign-up/'); ?>" style="color: #5c164c; text-decoration: none; margin-left: 8px;">Create a Registry</a>
              </td>
            </tr>
            <tr>
              <!-- Social Icons Column -->
              <td style="display: block; width: 100%; text-align: center;">
                <a href="https://www.instagram.com/modernlifafa?igsh=MTIwd3Q3OXI5eHFteA==" target="_blank" style="display: inline-block;">
                  <img src="<?php echo $insta; ?>" alt="Instagram" width="28" height="28" style="display: block; border: 0;">
                </a>
                <a href="https://www.facebook.com/share/1XfR4u8pcd/?mibextid=wwXIfr" target="_blank" style="display: inline-block;">
                  <img src="<?php echo $fb; ?>" alt="Facebook" width="28" height="28" style="display: block; border: 0;">
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Footer Bottom Row -->
      <tr>
        <td style="text-align: center; padding: 5px 0 0;">
          <p style="margin: 0; font-size: 14px; line-height: 20px; color: #5c164c; font-family: Segoe UI, Helvetica, sans-serif;">
            Modern Lifafa © <?php echo date("Y"); ?>, All Rights Reserved
          </p>
        </td>
      </tr>
    </table>
  </td>
</tr>


</table>
</body>

</html>