<?php
/**
 * User Search Handler
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

use QUICKAL\Interfaces\SearchHandlerInterface;
use QUICKAL\Config\PluginConfig;

class UserSearchHandler implements SearchHandlerInterface
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Search for users based on the given term
     *
     * @param string $term Search term
     * @return array Array of search results
     */
    public function search(string $term): array
    {
        try {
            $this->logger->debug('Searching users', ['term' => $term]);
            
            $options = PluginConfig::getAllOptions();
            $usersSearchEnabled = $options['users_search'] ?? false;
            
            if (!$usersSearchEnabled) {
                $this->logger->debug('User search is disabled');
                return [];
            }

            $users = get_users([
                'search' => sanitize_text_field($term),
                'number' => 10,
            ]);

            $results = [];
            foreach ($users as $user) {
                $results[] = $this->formatUserResult($user);
            }

            $this->logger->debug('User search completed', [
                'term' => $term,
                'found' => count($results)
            ]);

            return $results;

        } catch (\Exception $e) {
            $this->logger->logError($e);
            return [];
        }
    }

    /**
     * Format a user into a search result
     *
     * @param \WP_User $user WordPress user object
     * @return array Formatted search result
     */
    private function formatUserResult(\WP_User $user): array
    {
        return [
            'type' => 'user',
            'icon' => 'dashicons-admin-users',
            'label' => $user->display_name,
            'term' => strtolower($user->display_name),
            'link' => get_edit_user_link($user->ID),
            'id' => $user->ID,
        ];
    }
} 