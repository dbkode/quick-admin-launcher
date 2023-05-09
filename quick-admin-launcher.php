<?php
/**
 * Plugin Name: Quick Admin Launcher
 * Plugin URI:  quick-admin-launcher.com
 * Description: Quick Admin Launcher is a WordPress plugin that allows to quickly launch any admin tool from a search box.
 * Version:     1.0.0
 * Author:      dbeja
 * Text Domain: quickal
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'QUICKAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QUICKAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'QUICKAL_VERSION', '1.0.0' );

require_once QUICKAL_PLUGIN_DIR . '/includes/autoload.php';

register_activation_hook( __FILE__, 'quickal_activate' );
register_deactivation_hook( __FILE__, 'quickal_deactivate' );

/**
 * Activation hook
 *
 * @since 1.0.0
 */
function quickal_activate() {
	// set default quickal settings.
	$options = get_option( 'quickal_settings' );
	if ( false === $options ) {
		$default = array(
			'post_types'   => array( 'page', 'post' ),
			'users_search' => 1,
			'hotkey'       => array(
				'display' => 'CTRL + k',
				'key'     => 'k',
				'alt'     => '',
				'ctrl'    => 1,
				'shift'   => '',
				'meta'    => '',
			),
		);
		update_option( 'quickal_settings', $default );
	}
}

/**
 * Deactivation hook
 *
 * @since 1.0.0
 */
function quickal_deactivate() {}

$quickal = new QUICKAL\QuickAL();
$quickal->init();
