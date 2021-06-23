<?php
/**
 * Main Wipi class file
 *
 * @package Wipi
 * @subpackage Core
 * @since 1.0.0
 */

namespace WIPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wipi class.
 *
 * @since 1.0.0
 */
final class Wipi {

	/**
	 * Plugin initializer
	 *
	 * @since 1.0.0
	 */
	public function init() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'init', array( $this, 'setup' ) );
	}

	/**
	 * Activation hook
	 *
	 * @since 1.0.0
	 */
	public function activate() {}

	/**
	 * Deactivation hook
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {}

	/**
	 * Setup plugin.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Register rest functions.
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );

		// Add wipi modal to admin.
		add_action( 'admin_footer', array( $this, 'modal_html' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/dist/wipi.js', array(), WIPI_VERSION, false );

		wp_localize_script(
			'wipi-js',
			'wipiData',
			array(
				'rest'  => esc_url_raw( rest_url( 'wipi/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register API Routes for Wipi.
	 *
	 * @since 1.0.0
	 */
	public function register_api_routes() {

		// Search posts route.
		register_rest_route(
			'wipi/v1',
			'/search/(?P<term>\S+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_search' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);

	}

	/**
	 * Add Wipi Modal HTML to footer.
	 *
	 * @since 1.0.0
	 */
	public function modal_html() {
		include WIPI_PLUGIN_DIR . 'templates/wipi-modal.php';
	}

	/**
	 * Rest API route for searching on posts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data GET data.
	 * @return array Posts found.
	 */
	public function rest_search( $data ) {
		$term = $data['term'];

		// Search on posts.
		$posts = get_posts(
			array(
				's'         => $term,
				'post_type' => 'any',
			)
		);

		// Search on users.
		$users = get_users(
			array(
				'search' => $term,
			),
		);

		// Merge all results.
		$results = array();
		foreach ( $posts as $post ) {
			$results[] = array(
				'prefix' => $post->post_type,
				'label'  => $post->post_title,
				'term'   => strtolower( $post->post_title ),
				'link'   => get_edit_post_link( $post->ID, '' ),
			);
		}
		foreach ( $users as $user ) {
			$results[] = array(
				'prefix' => 'user',
				'label'  => $user->display_name,
				'term'   => strtolower( $user->display_name ),
				'link'   => get_edit_user_link( $user->ID ),
			);
		}

		return $results;
	}
}
