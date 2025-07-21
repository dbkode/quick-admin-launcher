<?php
/**
 * Admin Scripts Service
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

use QUICKAL\Config\PluginConfig;

class AdminScriptsService
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register WordPress hooks for admin scripts
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_filter('script_loader_tag', [$this, 'deferParsingOfJs'], 10, 2);
        add_action('admin_footer', [$this, 'addModalHtml']);
        add_action('admin_bar_menu', [$this, 'addAdminMenuItem'], 999);
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @return void
     */
    public function enqueueAdminScripts(): void
    {
        try {
            wp_enqueue_script(
                'quickal-react',
                QUICKAL_PLUGIN_URL . '/build/index.js',
                ['wp-element'],
                QUICKAL_VERSION,
                true
            );

            wp_enqueue_style(
                'quickal-react',
                QUICKAL_PLUGIN_URL . '/build/index.css',
                [],
                QUICKAL_VERSION
            );

            $this->localizeScript();

        } catch (\Exception $e) {
            $this->logger->logError($e);
        }
    }

    /**
     * Localize script with data for React app
     *
     * @return void
     */
    private function localizeScript(): void
    {
        $extraItems = apply_filters('quickal_extra_items', []);
        $options = PluginConfig::getAllOptions();
        
        $hotkey = [
            'key' => $options['hotkey']['key'] ?? 'k',
            'alt' => $options['hotkey']['alt'] ?? false,
            'ctrl' => $options['hotkey']['ctrl'] ?? true,
            'shift' => $options['hotkey']['shift'] ?? false,
            'meta' => $options['hotkey']['meta'] ?? false,
        ];

        wp_localize_script('quickal-react', 'quickalData', [
            'rest' => esc_url_raw(rest_url('quickal/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'extra_items' => $extraItems,
            'hotkey' => $hotkey,
            'version' => QUICKAL_VERSION,
        ]);
    }

    /**
     * Defer parsing of JavaScript for performance
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @return string Modified script tag
     */
    public function deferParsingOfJs(string $tag, string $handle): string
    {
        if (strpos($handle, 'quickal') !== false) {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }

    /**
     * Add modal HTML to admin footer
     *
     * @return void
     */
    public function addModalHtml(): void
    {
        echo '<div id="quickal-modal-root"></div>';
    }

    /**
     * Add admin bar menu item
     *
     * @param \WP_Admin_Bar $wpAdminBar WordPress admin bar object
     * @return void
     */
    public function addAdminMenuItem(\WP_Admin_Bar $wpAdminBar): void
    {
        $args = [
            'id' => 'quickal-admin-bar',
            'title' => '<span class="quickal-admin-bar-icon"></span>',
            'href' => '#',
            'meta' => [
                'class' => 'quickal-admin-bar',
                'title' => 'Quick Admin Launcher'
            ]
        ];
        $wpAdminBar->add_node($args);
    }
} 