<?php

defined('ABSPATH') || exit;

class ML_RPX_ADMIN
{
    /**
     * Initialize admin functionality.
     */
    public static function init()
    {
        add_action(
            'admin_menu',
            array(__CLASS__, 'admin_menu')
        );
    }

    /**
     * Add RazorpayX admin menu.
     */
    public static function admin_menu()
    {
        add_menu_page(
            'Modern Lifafa Payouts',
            'Custom RazorpayX Payouts',
            'manage_woocommerce',
            'modern-lifafa-payouts',
            array(__CLASS__, 'render_page'),
            'dashicons-money-alt',
            56
        );
    }

    /**
     * Render admin page.
     */
    public static function render_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('You do not have permission to access this page.');
        }

        ?>
        <div class="wrap">

            <h1>Modern Lifafa Payouts</h1>

            <p>
                RazorpayX payout management will appear here.
            </p>

            <hr>

            <h2>Configuration</h2>

            <?php if (
                defined('ML_RPX_KEY_ID') &&
                defined('ML_RPX_KEY_SECRET')
            ) : ?>

                <p style="color:green;">
                    ✓ RazorpayX API credentials are configured.
                </p>

            <?php else : ?>

                <p style="color:red;">
                    ✗ RazorpayX API credentials are not configured.
                </p>

                <p>
                    Add ML_RPX_KEY_ID and ML_RPX_KEY_SECRET
                    to wp-config.php.
                </p>

            <?php endif; ?>

        </div>
        <?php
    }
}

ML_RPX_ADMIN::init();