<?php
/**
 * Sample implementation of the Custom Header feature
 *
 * You can add an optional custom header image to header.php like so ...
 *
	<?php the_header_image_tag(); ?>
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 * @package modern_lifafa
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses modern_lifafa_header_style()
 */
function modern_lifafa_custom_header_setup() {
	add_theme_support(
		'custom-header',
		apply_filters(
			'modern_lifafa_custom_header_args',
			array(
				'default-image'      => '',
				'default-text-color' => '000000',
				'width'              => 1000,
				'height'             => 250,
				'flex-height'        => true,
				'wp-head-callback'   => 'modern_lifafa_header_style',
			)
		)
	);
}
add_action( 'after_setup_theme', 'modern_lifafa_custom_header_setup' );

if ( ! function_exists( 'modern_lifafa_header_style' ) ) :
	/**
	 * Styles the header image and text displayed on the blog.
	 *
	 * @see modern_lifafa_custom_header_setup().
	 */
	function modern_lifafa_header_style() {
		$header_text_color = get_header_textcolor();

		/*
		 * If no custom options for text are set, let's bail.
		 * get_header_textcolor() options: Any hex value, 'blank' to hide text. Default: add_theme_support( 'custom-header' ).
		 */
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If we get this far, we have custom styles. Let's do this.
		?>
		<style type="text/css">
		<?php
		// Has the text been hidden?
		if ( ! display_header_text() ) :
			?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
				}
			<?php
			// If the user has set a custom color for the text use that.
		else :
			?>
			.site-title a,
			.site-description {
				color: #<?php echo esc_attr( $header_text_color ); ?>;
			}
		<?php endif; ?>
		</style>
		<?php
	}
endif;


// Register custom menus
function morden_lifafa_custom_new_menu() {
    register_nav_menus(
        array(
            'primary-menu2' => 'Primary Menu 2',
            'secondary-menu' => 'Secondary Menu',
            'secondary-logout-menu' => 'Secondary Menu 2',
            'footer_quick_menu' => 'Quick Menu',
            'footer_other_links' => 'Other Links',
        )
    );
}
add_action( 'init', 'morden_lifafa_custom_new_menu' );

// Custom Breadcrum 
function morden_lifafa_breadcrumbs() {
    $separator = ' &gt; ';
    $home_title = 'Home';
    echo '<div class="breadcrumb-section">';
    echo '<div class="container">';
    echo '<ul class="breadcrumb">';
    if ( is_page()) {
        $title = get_the_title();
        echo '<li><a href="'.get_home_url().'">'.$home_title.'</a></li>';
        echo '<li class="active" aria-current="page">'.$title.'</li>';
    }
    if ( is_archive()) {
        $pt = get_post_type_object(get_post_type());
        $title = get_post_type();
        echo '<li><a href="'.get_home_url().'">'.$home_title.'</a></li>';
        echo '<li class="active" aria-current="page">'.$pt->labels->name.'</li>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';
}

// Walker menu 
class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $class_names = 'dropdown-menu';
        $output .= "\n$indent<ul class=\"$class_names\">\n";
    }
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = join(' ', array_filter($classes));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        $output .= '<li' . $class_names .'>';
        $has_children = in_array('menu-item-has-children', $classes);
        $attributes = ! empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= ! empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= ! empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= ! empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= $has_children ? ' class="nav-link dropdown-toggle"' : '';
        $attributes .= $has_children ? 'id="category'.$item->attr_title.'" role="button" data-bs-toggle="dropdown" aria-expanded="false"' : '';
        if($item->ID == 496){
            $attributes .= "data-bs-toggle='modal' data-bs-target='#findCoupleModel'";
        }
        if ($depth > 0) {
            $attributes .= ' class="dropdown-item"';
        } else {
            $attributes .= ' class="nav-link"';
        }
        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}

add_filter('wp_nav_menu_items', 'append_logout_link_to_last_submenu_item', 10, 2);
function append_logout_link_to_last_submenu_item($items, $args) {
    error_log($args->theme_location);
    if ($args->theme_location == 'secondary-logout-menu' || $args->theme_location == 'primary-menu2') {
        $logout_link = '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . esc_url(wp_logout_url(home_url())) . '">Logout</a></li>';
        $submenu_class = 'dropdown-menu';
        $pattern = '/(<ul[^>]*?class="' . $submenu_class . '[^>]*?">)(.*?)(<\/ul>)(?!.*<ul[^>]*?class="' . $submenu_class . '[^>]*?">)/is';
        $replacement = '$1$2' . $logout_link . '$3';
        $items = preg_replace($pattern, $replacement, $items, 1);
    }
    return $items;
}