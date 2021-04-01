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

		// add wipi modal to admin.
		add_action( 'admin_footer', array( $this, 'modal_html' ) );
	}

	public function admin_scripts() {
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/dist/wipi.js' );
	}

	public function modal_html() {
		include WIPI_PLUGIN_DIR . 'templates/wipi-modal.php';
	}
}
