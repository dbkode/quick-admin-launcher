<?php
/**
 * Search Service
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

use QUICKAL\Interfaces\SearchHandlerInterface;
use QUICKAL\Config\PluginConfig;

class SearchService
{
    private PostSearchHandler $postSearchHandler;
    private UserSearchHandler $userSearchHandler;
    private LoggerService $logger;

    public function __construct(
        PostSearchHandler $postSearchHandler,
        UserSearchHandler $userSearchHandler,
        LoggerService $logger
    ) {
        $this->postSearchHandler = $postSearchHandler;
        $this->userSearchHandler = $userSearchHandler;
        $this->logger = $logger;
    }

    /**
     * Register WordPress hooks for the search service
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerApiRoutes']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function registerApiRoutes(): void
    {
        register_rest_route('quickal/v1', '/search/(?P<term>\S+)', [
            'methods' => 'GET',
            'callback' => [$this, 'handleSearch'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'term' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return !empty($param) && strlen($param) >= 2;
                    }
                ]
            ]
        ]);
    }

    /**
     * Handle search request
     *
     * @param \WP_REST_Request $request REST request object
     * @return \WP_REST_Response Response object
     */
    public function handleSearch(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $term = $request->get_param('term');
            $this->logger->debug('Processing search request', ['term' => $term]);

            $results = [];
            $results = array_merge($results, $this->postSearchHandler->search($term));
            $results = array_merge($results, $this->userSearchHandler->search($term));

            // Apply filters for extensibility
            $results = apply_filters('quickal_server_search_results', $results, $term);

            $this->logger->debug('Search completed', [
                'term' => $term,
                'total_results' => count($results)
            ]);

            return new \WP_REST_Response($results, 200);

        } catch (\Exception $e) {
            $this->logger->logError($e);
            return new \WP_REST_Response([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has permission to perform search
     *
     * @return bool Permission status
     */
    public function checkPermissions(): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * Get search statistics
     *
     * @return array Search statistics
     */
    public function getSearchStats(): array
    {
        $options = PluginConfig::getAllOptions();
        
        return [
            'post_types_enabled' => $options['post_types'] ?? [],
            'user_search_enabled' => $options['users_search'] ?? false,
            'total_post_types' => count($options['post_types'] ?? []),
        ];
    }
} 