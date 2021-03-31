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
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/assets/dist/wipi.js' );
	}

	public function modal_html() {
		?>
		<div id="wipi-modal" x-data="wipi()" x-init="init" x-show="modal" style="position: fixed; top: 50%; left: 50%; width: 300px; height: 100px; background-color: yellow; display: none;">
		</div>
		<?php
	}
}
