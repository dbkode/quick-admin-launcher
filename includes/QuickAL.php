<?php
/**
 * Main QuickAL class file
 *
 * @package QuickAL
 * @subpackage Core
 * @since 1.0.0
 */

namespace QUICKAL;

use QUICKAL\Services\SearchService;
use QUICKAL\Services\AdminScriptsService;
use QUICKAL\Services\SettingsService;
use QUICKAL\Services\LoggerService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * QuickAL class - Coordinator only
 *
 * @since 1.0.0
 */
final class QuickAL
{
    private SearchService $searchService;
    private AdminScriptsService $adminScriptsService;
    private SettingsService $settingsService;
    private LoggerService $logger;

    public function __construct(
        SearchService $searchService,
        AdminScriptsService $adminScriptsService,
        SettingsService $settingsService,
        LoggerService $logger
    ) {
        $this->searchService = $searchService;
        $this->adminScriptsService = $adminScriptsService;
        $this->settingsService = $settingsService;
        $this->logger = $logger;
    }

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        add_action('init', [$this, 'setup']);
    }

    /**
     * Setup plugin components by delegating to services
     */
    public function setup(): void
    {
        // Load text domain for internationalization
        load_plugin_textdomain('quickal', false, QUICKAL_PLUGIN_DIR . '/languages');
        $this->searchService->registerHooks();
        $this->adminScriptsService->registerHooks();
        $this->settingsService->registerHooks();
    }
}
