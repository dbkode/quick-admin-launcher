<?php
/**
 * Plugin Name: Quick Admin Launcher
 * Plugin URI:  https://wordpress.org/plugins/quick-admin-launcher/
 * Description: Quick Admin Launcher is a WordPress plugin that allows to quickly launch any admin tool from a search box.
 * Version:     1.1.1
 * Author:      dbeja
 * Text Domain: quickal
 * Domain Path: /languages
 * Requires PHP: 7.4
 */

defined('ABSPATH') || die('No script kiddies please!');

define('QUICKAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QUICKAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QUICKAL_VERSION', '1.1.1');

// Load Composer autoloader
require_once QUICKAL_PLUGIN_DIR . '/vendor/autoload.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'quickal_activate');
register_deactivation_hook(__FILE__, 'quickal_deactivate');

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function quickal_activate(): void
{
    try {
        // Set default settings if they don't exist
        $options = get_option('quickal_settings');
        if (false === $options) {
            $defaults = \QUICKAL\Config\PluginConfig::getDefaults();
            update_option('quickal_settings', $defaults);
        }
        
        // Log activation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[QuickAL] Plugin activated successfully');
        }
        
    } catch (\Exception $e) {
        error_log('[QuickAL] Activation error: ' . $e->getMessage());
    }
}

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
function quickal_deactivate(): void
{
    try {
        // Clean up any temporary data if needed
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[QuickAL] Plugin deactivated');
        }
        
    } catch (\Exception $e) {
        error_log('[QuickAL] Deactivation error: ' . $e->getMessage());
    }
}

/**
 * Initialize the plugin with dependency injection
 *
 * @return void
 */
function quickal_init(): void
{
    try {
        // Create dependency injection container
        $container = new \QUICKAL\DI\Container();
        
        // Get the main plugin instance
        $plugin = $container->get(\QUICKAL\QuickAL::class);
        
        // Initialize the plugin
        $plugin->init();
        
    } catch (\Exception $e) {
        error_log('[QuickAL] Initialization error: ' . $e->getMessage());
    }
}

// Initialize plugin after WordPress is loaded
add_action('plugins_loaded', 'quickal_init');
