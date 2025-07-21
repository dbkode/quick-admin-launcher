<?php
/**
 * Post Search Handler
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

use QUICKAL\Interfaces\SearchHandlerInterface;
use QUICKAL\Config\PluginConfig;

class PostSearchHandler implements SearchHandlerInterface
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Search for posts based on the given term
     *
     * @param string $term Search term
     * @return array Array of search results
     */
    public function search(string $term): array
    {
        try {
            $this->logger->debug('Searching posts', ['term' => $term]);
            
            $options = PluginConfig::getAllOptions();
            $postTypes = $options['post_types'] ?? ['page', 'post'];
            
            if (empty($postTypes)) {
                $this->logger->debug('No post types configured for search');
                return [];
            }

            $posts = get_posts([
                's' => sanitize_text_field($term),
                'post_type' => $postTypes,
                'post_status' => 'publish',
                'numberposts' => 10,
            ]);

            $results = [];
            foreach ($posts as $post) {
                $results[] = $this->formatPostResult($post);
            }

            $this->logger->debug('Post search completed', [
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
     * Format a post into a search result
     *
     * @param \WP_Post $post WordPress post object
     * @return array Formatted search result
     */
    private function formatPostResult(\WP_Post $post): array
    {
        $postType = get_post_type_object($post->post_type);
        $icon = $this->getPostTypeIcon($postType);

        return [
            'type' => $post->post_type,
            'icon' => $icon,
            'label' => $post->post_title,
            'term' => strtolower($post->post_title),
            'link' => get_edit_post_link($post->ID, ''),
            'id' => $post->ID,
        ];
    }

    /**
     * Get the appropriate icon for a post type
     *
     * @param \WP_Post_Type|null $postType Post type object
     * @return string Icon class or URL
     */
    private function getPostTypeIcon($postType): string
    {
        if (!$postType) {
            return 'dashicons-admin-post';
        }

        $icon = $postType->menu_icon ?? 'dashicons-admin-post';

        if (is_string($icon)) {
            if (strpos($icon, 'data:image/svg+xml;base64,') === 0 || 
                strpos($icon, 'dashicons-') === 0) {
                return $icon;
            } else {
                return esc_url($icon);
            }
        }

        return 'dashicons-admin-post';
    }
} 