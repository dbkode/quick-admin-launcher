<?php
/**
 * Plugin Name: WP Admin Launcher
 * Plugin URI:  wp-admin-launcher.com
 * Description: WP Admin Launcher is a WordPress plugin that allows to quickly launch any admin tool from a search box.
 * Version:     1.0.0
 * Author:      dbeja
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'WPAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPAL_VERSION', '0.0.1' );

require_once WPAL_PLUGIN_DIR . '/includes/autoload.php';

register_activation_hook( __FILE__, 'wpal_activate' );
register_deactivation_hook( __FILE__, 'wpal_deactivate' );

/**
 * Activation hook
 *
 * @since 1.0.0
 */
function wpal_activate() {
	// set default wpal settings.
	$options = get_option( 'wpal_settings' );
	if ( false === $options ) {
		$default = array(
			'post_types'   => array( 'page', 'post' ),
			'users_search' => 1,
			'hotkey'       => array(
				'display' => 'CTRL + Space',
				'alt'     => '',
				'ctrl'    => 1,
				'shift'   => '',
				'meta'    => '',
			),
		);
		update_option( 'wpal_settings', $default );
	}
}

/**
 * Deactivation hook
 *
 * @since 1.0.0
 */
function wpal_deactivate() {}

$wpal = new WPAL\Wpal();
$wpal->init();
