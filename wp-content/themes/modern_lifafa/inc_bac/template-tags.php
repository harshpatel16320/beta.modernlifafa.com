<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package modern_lifafa
 */

if ( ! function_exists( 'modern_lifafa_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function modern_lifafa_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'modern_lifafa' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'modern_lifafa_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function modern_lifafa_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'modern_lifafa' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'modern_lifafa_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function modern_lifafa_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'modern_lifafa' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'modern_lifafa' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'modern_lifafa' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'modern_lifafa' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'modern_lifafa' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'modern_lifafa' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'modern_lifafa_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function modern_lifafa_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						'post-thumbnail',
						array(
							'alt' => the_title_attribute(
								array(
									'echo' => false,
								)
							),
						)
					);
				?>
			</a>

			<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;

// Add Brand taxonomy in Products
function create_brand_taxonomy() {
    $labels = array(
        'name'              => _x( 'Brands', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Brand', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Brands', 'textdomain' ),
        'all_items'         => __( 'All Brands', 'textdomain' ),
        'parent_item'       => __( 'Parent Brand', 'textdomain' ),
        'parent_item_colon' => __( 'Parent Brand:', 'textdomain' ),
        'edit_item'         => __( 'Edit Brand', 'textdomain' ),
        'update_item'       => __( 'Update Brand', 'textdomain' ),
        'add_new_item'      => __( 'Add New Brand', 'textdomain' ),
        'new_item_name'     => __( 'New Brand Name', 'textdomain' ),
        'menu_name'         => __( 'Brand', 'textdomain' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'brand' ),
    );
    register_taxonomy( 'brand', array( 'product' ), $args );
}
add_action( 'init', 'create_brand_taxonomy', 0 );

// Handle form submission and reCAPTCHA verification
function handle_custom_contact_form_submission() {
    if ( isset( $_POST['custom_contact_form_nonce_field'] ) && wp_verify_nonce( $_POST['custom_contact_form_nonce_field'], 'custom_contact_form_nonce' ) ) {
        $recaptcha_secret = '6LcGPwYqAAAAAM4rPEXsGP9iVZqXqAtBSQwsWPeh';
        $recaptcha_response = $_POST['g-recaptcha-response'];
        $remote_ip = $_SERVER['REMOTE_ADDR'];

        $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}&remoteip={$remote_ip}";
        $recaptcha_response = wp_remote_get( $recaptcha_url );
        if ( is_wp_error( $recaptcha_response ) ) {
            echo 'Error: Could not verify reCAPTCHA.';
            return;
        }
        $recaptcha_data = json_decode( $recaptcha_response['body'] );
        if ( ! $recaptcha_data->success ) {
            echo 'reCAPTCHA verification failed. Please try again.';
            return;
        }
    } else {
        echo 'Security check failed. Please try again.';
    }
}
add_action( 'admin_post_nopriv_custom_contact_form', 'handle_custom_contact_form_submission' );
add_action( 'admin_post_custom_contact_form', 'handle_custom_contact_form_submission' );
