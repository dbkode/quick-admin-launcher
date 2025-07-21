<?php
/**
 * Settings Service
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

use QUICKAL\Config\PluginConfig;

class SettingsService
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register WordPress hooks for settings
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Add settings link to plugin page
        $pluginDirName = basename(QUICKAL_PLUGIN_DIR);
        add_filter(
            'plugin_action_links_' . $pluginDirName . '/quick-admin-launcher.php',
            [$this, 'addSettingsLink']
        );
    }

    /**
     * Add settings page to admin menu
     *
     * @return void
     */
    public function addSettingsPage(): void
    {
        add_options_page(
            __('Quick Admin Launcher', 'quickal'),
            __('Quick Admin Launcher', 'quickal'),
            'manage_options',
            'quickal-settings',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render the settings page
     *
     * @return void
     */
    public function renderSettingsPage(): void
    {
        ?>
        <div class="wrap">
            <h2 class="quickal-settings-title">
                <?php esc_html_e('Quick Admin Launcher Settings', 'quickal'); ?>
            </h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('quickal_settings');
                do_settings_sections('quickal_settings');
                ?>
                <input name="submit" class="button button-primary" type="submit" 
                       value="<?php esc_attr_e('Save', 'quickal'); ?>" />
            </form>
        </div>
        <?php
    }

    /**
     * Register settings fields
     *
     * @return void
     */
    public function registerSettings(): void
    {
        register_setting('quickal_settings', 'quickal_settings', [
            'sanitize_callback' => [$this, 'sanitizeSettings']
        ]);
        
        add_settings_section(
            'quickal_settings_section',
            '',
            '__return_true',
            'quickal_settings'
        );

        // Post types setting
        add_settings_field(
            'quickal_setting_post_types',
            __('Post Types', 'quickal'),
            [$this, 'renderPostTypesSetting'],
            'quickal_settings',
            'quickal_settings_section'
        );

        // Users search setting
        add_settings_field(
            'quickal_setting_users_search',
            __('Enable Users Search', 'quickal'),
            [$this, 'renderUsersSearchSetting'],
            'quickal_settings',
            'quickal_settings_section'
        );

        // Hotkey setting
        add_settings_field(
            'quickal_setting_hotkey',
            __('Hotkey', 'quickal'),
            [$this, 'renderHotkeySetting'],
            'quickal_settings',
            'quickal_settings_section'
        );
    }

    /**
     * Sanitize settings before saving
     *
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function sanitizeSettings(array $input): array
    {
        $sanitized = [];
        
        // Sanitize post types
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_text_field', $input['post_types']);
        }
        
        // Sanitize users search
        $sanitized['users_search'] = isset($input['users_search']) ? true : false;
        
        // Sanitize hotkey settings
        if (isset($input['hotkey']) && is_array($input['hotkey'])) {
            $sanitized['hotkey'] = [
                'key' => sanitize_text_field($input['hotkey']['key'] ?? 'k'),
                'alt' => !empty($input['hotkey']['alt']),
                'ctrl' => !empty($input['hotkey']['ctrl']),
                'shift' => !empty($input['hotkey']['shift']),
                'meta' => !empty($input['hotkey']['meta']),
            ];
        }
        
        $this->logger->debug('Settings sanitized', $sanitized);
        return $sanitized;
    }

    /**
     * Render post types setting field
     *
     * @return void
     */
    public function renderPostTypesSetting(): void
    {
        $options = PluginConfig::getAllOptions();
        $value = $options['post_types'] ?? [];
        
        $postTypes = get_post_types(['public' => true], 'objects');
        ?>
        <fieldset>
            <?php foreach ($postTypes as $postType): ?>
                <label for="quickal_setting_post_type_<?php echo esc_attr($postType->name); ?>">
                    <input type="checkbox"
                           id="quickal_setting_post_type_<?php echo esc_attr($postType->name); ?>"
                           name="quickal_settings[post_types][]"
                           value="<?php echo esc_attr($postType->name); ?>"
                           <?php echo in_array($postType->name, $value, true) ? 'checked' : ''; ?> />
                    <?php echo esc_html($postType->label); ?>
                </label>
                <br>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    /**
     * Render users search setting field
     *
     * @return void
     */
    public function renderUsersSearchSetting(): void
    {
        $options = PluginConfig::getAllOptions();
        $value = $options['users_search'] ?? false;
        ?>
        <fieldset>
            <label for="quickal_setting_users_search">
                <input type="checkbox"
                       id="quickal_setting_users_search"
                       name="quickal_settings[users_search]"
                       value="1"
                       <?php checked(true, $value); ?> />
                <?php esc_html_e('Enable searching for users in the QuickAL interface.', 'quickal'); ?>
            </label>
        </fieldset>
        <?php
    }

    /**
     * Render hotkey setting field
     *
     * @return void
     */
    public function renderHotkeySetting(): void
    {
        $options = PluginConfig::getAllOptions();
        $hotkey = $options['hotkey'] ?? [];
        ?>
        <fieldset>
            <input type="hidden" id="quickal_setting_hotkey_key" 
                   name="quickal_settings[hotkey][key]" 
                   value="<?php echo esc_attr($hotkey['key'] ?? 'k'); ?>">
            <input type="hidden" id="quickal_setting_hotkey_alt" 
                   name="quickal_settings[hotkey][alt]" 
                   value="<?php echo esc_attr($hotkey['alt'] ?? ''); ?>">
            <input type="hidden" id="quickal_setting_hotkey_ctrl" 
                   name="quickal_settings[hotkey][ctrl]" 
                   value="<?php echo esc_attr($hotkey['ctrl'] ?? ''); ?>">
            <input type="hidden" id="quickal_setting_hotkey_shift" 
                   name="quickal_settings[hotkey][shift]" 
                   value="<?php echo esc_attr($hotkey['shift'] ?? ''); ?>">
            <input type="hidden" id="quickal_setting_hotkey_meta" 
                   name="quickal_settings[hotkey][meta]" 
                   value="<?php echo esc_attr($hotkey['meta'] ?? ''); ?>">
            
            <label for="quickal_setting_hotkey_display">
                <input type="text"
                       id="quickal_setting_hotkey_display"
                       name="quickal_settings[hotkey_display]"
                       value="<?php echo esc_attr($this->formatHotkeyDisplay($hotkey)); ?>"
                       readonly>
            </label>
            <br>
            <i><?php esc_html_e('Click this input and press a combination of keys to set the QuickAL hotkey.', 'quickal'); ?></i>
        </fieldset>

        <script>
            document.getElementById('quickal_setting_hotkey_display').onkeydown = function(e) {
                e.preventDefault();
                var value = e.code.replace('Key', '');
                var modifiers = [];
                
                if (e.altKey) modifiers.push('ALT');
                if (e.ctrlKey) modifiers.push('CTRL');
                if (e.shiftKey) modifiers.push('SHIFT');
                if (e.metaKey) modifiers.push('META');
                
                if (modifiers.length > 0) {
                    value = modifiers.join(' + ') + ' + ' + value;
                }
                
                this.value = value;

                // Update hidden inputs
                document.getElementById('quickal_setting_hotkey_key').value = e.key;
                document.getElementById('quickal_setting_hotkey_alt').value = e.altKey ? '1' : '';
                document.getElementById('quickal_setting_hotkey_ctrl').value = e.ctrlKey ? '1' : '';
                document.getElementById('quickal_setting_hotkey_shift').value = e.shiftKey ? '1' : '';
                document.getElementById('quickal_setting_hotkey_meta').value = e.metaKey ? '1' : '';

                return false;
            };
        </script>
        <?php
    }

    /**
     * Format hotkey for display
     *
     * @param array $hotkey Hotkey settings
     * @return string Formatted hotkey string
     */
    private function formatHotkeyDisplay(array $hotkey): string
    {
        $modifiers = [];
        if (!empty($hotkey['alt'])) $modifiers[] = 'ALT';
        if (!empty($hotkey['ctrl'])) $modifiers[] = 'CTRL';
        if (!empty($hotkey['shift'])) $modifiers[] = 'SHIFT';
        if (!empty($hotkey['meta'])) $modifiers[] = 'META';
        
        $key = strtoupper($hotkey['key'] ?? 'K');
        
        if (!empty($modifiers)) {
            return implode(' + ', $modifiers) . ' + ' . $key;
        }
        
        return $key;
    }

    /**
     * Add settings link to plugin page
     *
     * @param array $links Plugin action links
     * @return array Modified links
     */
    public function addSettingsLink(array $links): array
    {
        $settingsLink = '<a href="options-general.php?page=quickal-settings">' . __('Settings') . '</a>';
        $links[] = $settingsLink;
        return $links;
    }
} 