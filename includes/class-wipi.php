<?php

namespace WIPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wipi class.
 *
 * @since 0.0.1
 */
class Wipi {

	public function init() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'init', array( $this, 'setup' ) );
	}

	public function activate() {}

	public function deactivate() {}

	public function setup() {
		// enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// rest functions.
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );

		// add wipi modal to admin.
		add_action( 'admin_footer', array( $this, 'modal_html' ) );

		add_action( 'admin_menu', array( $this, 'test_menu' ) );
	}

	public function test_menu() {
		add_menu_page( 'TEST', 'TEST', 'manage_options', 'test-menu', function() {
			esc_html_e( 'HELLO!!!!', 'wipi' );
		}, 'dashicons-visibility');
	}

	public function admin_scripts() {
		$admin_menu = $this->get_admin_menu();
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/dist/wipi.js' );

		wp_localize_script(
			'wipi-js',
			'wipiData',
			array(
				'rest'       => esc_url_raw( rest_url( 'wipi/v1' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'admin_menu' => $admin_menu,
			)
		);
	}

		/*
	* Register API Routes
	*/
	public function register_api_routes() {
		// search.
		register_rest_route( 'wipi/v1', '/search/(?P<term>\S+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'rest_search' ),
			'permission_callback' => function () {
				return true;
			},
		) );
	}

	public function modal_html() {
		include WIPI_PLUGIN_DIR . 'templates/wipi-modal.php';
	}

	public function rest_search( $data ) {
		$term = $data['term'];

		$posts = get_posts(
			array(
				's'         => $term,
				'post_type' => 'any',
			)
		);

		$users = get_users(
			array(
				'search' => $term,
			),
		);

		$results = array();
		foreach ( $posts as $post ) {
			$results[] = array(
				'prefix'  => $post->post_type,
				'label'   => $post->post_title,
				'labelLC' => strtolower( $post->post_title ),
				'href'    => get_edit_post_link( $post->ID, '' ),
			);
		}
		foreach ( $users as $user ) {
			$results[] = array(
				'prefix'  => 'user',
				'label'   => $user->display_name,
				'labelLC' => strtolower( $user->display_name ),
				'href'    => get_edit_user_link( $user->ID ),
			);
		}

		return $results;
	}

	private function get_admin_menu() {
		global $submenu, $menu;

		$admin_menu = array();
		$remove_tags_regex = '/<[^>]*>[^<]*<[^>]*>/';
		foreach ( $menu as $key => $item ) {
			// check if separator.
			if ( ! empty( $item[4] ) && false !== strpos( $item[4], 'wp-menu-separator' ) ) {
				continue;
			}

			$label = preg_replace( $remove_tags_regex, '', $item[0] );
			$icon  = isset( $item[6] ) ? $item[6] : '';

			// get link.
			$menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
			$menu_file = $item[2];
			$pos       = strpos( $menu_file, '?' );

			if ( false !== $pos ) {
				$menu_file = substr( $menu_file, 0, $pos );
			}

			$link = "{$item[2]}";
			if ( ! empty( $menu_hook )
				|| ( ( 'index.php' !== $item[2] )
					&& file_exists( WP_PLUGIN_DIR . "/$menu_file" )
					&& ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) )
			) {
				$link = "admin.php?page={$item[2]}";
			}

			$admin_menu[] = array(
				'label'   => $label,
				'labelLC' => strtolower( $label ),
				'href'    => $link,
				'icon'    => $icon,
			);

			if ( ! empty( $submenu[ $item[2] ] ) ) {
				$submenu_items = $submenu[ $item[2] ];

				foreach ( $submenu_items as $submenu_item ) {
					$sub_label = preg_replace( $remove_tags_regex, '', $submenu_item[0] );
					$sub_link = "/wp-admin/{$submenu_item[2]}";
					$sub_label_final = "{$label} - {$sub_label}";
					$admin_menu[] = array(
						'label'   => $sub_label_final,
						'labelLC' => strtolower( $sub_label_final ),
						'href'    => $sub_link,
						'icon'    => $icon,
					);
				}
			}
		}

		return $admin_menu;
	}
}
