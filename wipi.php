<?php
/**
 * Plugin Name:  Wipi
 * Plugin URI:   wipi.com
 * Description:  WP launcher
 * Version:      0.0.1
 * Author:       dbeja
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'WIPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WIPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WIPI_VERSION', '0.0.1' );

require_once WIPI_PLUGIN_DIR . '/includes/autoload.php';

register_activation_hook( __FILE__, 'wipi_activate' );
register_deactivation_hook( __FILE__, 'wipi_deactivate' );

/**
 * Activation hook
 *
 * @since 1.0.0
 */
function wipi_activate() {
	// set default wipi settings.
	$options = get_option( 'wipi_settings' );
	if ( false === $options ) {
		$default = array(
			'post_types' => array( 'page', 'post' ),
		);
		update_option( 'wipi_settings', $default );
	}
}

/**
 * Deactivation hook
 *
 * @since 1.0.0
 */
function wipi_deactivate() {}

$wipi = new WIPI\Wipi();
$wipi->init();
