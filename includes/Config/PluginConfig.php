<?php
/**
 * Plugin Configuration Management
 *
 * @package QuickAL
 * @subpackage Config
 * @since 1.0.0
 */

namespace QUICKAL\Config;

class PluginConfig
{
    private const DEFAULT_SETTINGS = [
        'post_types' => ['page', 'post'],
        'users_search' => true,
        'hotkey' => [
            'key' => 'k',
            'ctrl' => true,
            'alt' => false,
            'shift' => false,
            'meta' => false,
        ]
    ];

    /**
     * Get default plugin settings
     *
     * @return array Default settings
     */
    public static function getDefaults(): array
    {
        return self::DEFAULT_SETTINGS;
    }

    /**
     * Get a specific option value
     *
     * @param string $key Option key
     * @param mixed $default Default value if option doesn't exist
     * @return mixed Option value
     */
    public static function getOption(string $key, $default = null)
    {
        $options = get_option('quickal_settings', []);
        return $options[$key] ?? $default;
    }

    /**
     * Get all plugin options
     *
     * @return array All plugin options
     */
    public static function getAllOptions(): array
    {
        return get_option('quickal_settings', self::getDefaults());
    }

    /**
     * Update plugin options
     *
     * @param array $options Options to update
     * @return bool Success status
     */
    public static function updateOptions(array $options): bool
    {
        return update_option('quickal_settings', $options);
    }
} 