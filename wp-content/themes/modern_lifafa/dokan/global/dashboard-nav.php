<?php
$home_url     = home_url();
$active_class = ' class="active"'
?>

<div class="dokan-dash-sidebar">
    <div class="dashboard_logo_wrap">
        <a class="navbar-brand" href="<?php echo home_url(); ?>">
            <picture>
                <?php $custom_logo_id = get_theme_mod( 'custom_logo' );
                $image = wp_get_attachment_image_src( $custom_logo_id , 'full' ); ?>
                <img src="<?php echo $image[0]; ?>" alt="brand-logo" width="103" height="70">
            </picture>
        </a>
</div>
    <?php
    global $allowedposttags;
    do_action( 'dokan_dashboard_sidebar_start' );

    // These are required for the hamburger menu.
    if ( is_array( $allowedposttags ) ) {
        $allowedposttags['input'] = [ // phpcs:ignore
            'id'      => [],
            'type'    => [],
            'checked' => [],
        ];
        $allowedposttags['svg'] = [ // phpcs:ignore
            'fill'        => [],
            'role'        => [],
            'xmlns'       => [],
            'width'       => [],
            'height'      => [],
            'viewbox'     => [],
            'focusable'   => [],
            'aria-hidden' => [],
        ];
        $allowedposttags['path'] = [ // phpcs:ignore
            'd'    => [],
            'fill' => [],
        ];
    }

    echo wp_kses( dokan_dashboard_nav( $active_menu ), $allowedposttags );

    do_action( 'dokan_dashboard_sidebar_end' );
    ?>
</div>
