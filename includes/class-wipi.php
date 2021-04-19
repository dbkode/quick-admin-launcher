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
	}

	public function admin_scripts() {
		//$admin_menu = $this->get_admin_menu();
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/dist/wipi.js' );

		wp_localize_script(
			'wipi-js',
			'wipiData',
			array(
				'rest'  => esc_url_raw( rest_url( 'wipi/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
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

		$results = array();
		foreach ( $posts as $post ) {
			$results[] = array(
				'label'   => "[$post->post_type] {$post->post_title}",
				'labelLC' => strtolower( $post->post_title ),
				'href'    => get_edit_post_link( $post->ID, '' ),
			);
		}

		return $results;
	}

	private function get_admin_menu() {
		global $submenu, $menu;

		$admin_menu = array();
		foreach ( $menu as $key => $item ) {
			// check if separator.
			if ( ! empty( $item[4] ) && false !== strpos( $item[4], 'wp-menu-separator' ) ) {
				continue;
			}

			$admin_menu[] = array(
				'label' => $item[0],
				'link'  => '',
				'icon'  => '',
			);

			if ( ! empty( $submenu[ $item[2] ] ) ) {
				$submenu_items = $submenu[ $item[2] ];

				foreach ( $submenu_items as $submenu_item ) {
					error_log('--- SUBITEM');
					error_log( print_r( $submenu_item, true ) );
				}
			}
		}
	}
}
