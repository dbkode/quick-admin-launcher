<?php
use PHPUnit\Framework\MockObject\MockObject;
use QUICKAL\Services\SearchService;
use QUICKAL\Services\PostSearchHandler;
use QUICKAL\Services\UserSearchHandler;
use QUICKAL\Services\LoggerService;
use QUICKAL\Config\PluginConfig;

/**
 * @group quickal
 */
class SearchServiceTest extends WP_UnitTestCase {
    /** @var MockObject */
    private $postSearchHandler;
    /** @var MockObject */
    private $userSearchHandler;
    /** @var MockObject */
    private $logger;
    /** @var SearchService */
    private $service;

    public function setUp(): void {
        parent::setUp();
        $this->postSearchHandler = $this->createMock(PostSearchHandler::class);
        $this->userSearchHandler = $this->createMock(UserSearchHandler::class);
        $this->logger = $this->createMock(LoggerService::class);
        $this->service = new SearchService(
            $this->postSearchHandler,
            $this->userSearchHandler,
            $this->logger
        );
    }

    public function test_handle_search_success() {
        $request = $this->getMockBuilder('WP_REST_Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('get_param')->with('term')->willReturn('test');

        $this->postSearchHandler->expects($this->once())
            ->method('search')->with('test')->willReturn([
                ['type' => 'post', 'label' => 'Test Post']
            ]);
        $this->userSearchHandler->expects($this->once())
            ->method('search')->with('test')->willReturn([
                ['type' => 'user', 'label' => 'Test User']
            ]);
        $this->logger->expects($this->atLeastOnce())
            ->method('debug');

        add_filter('quickal_server_search_results', function($results, $term) {
            // Simulate filter adding an extra result
            $results[] = ['type' => 'custom', 'label' => 'Filtered'];
            return $results;
        }, 10, 2);

        $response = $this->service->handleSearch($request);
        $data = $response->get_data();
        $this->assertCount(3, $data);
        $this->assertEquals('Test Post', $data[0]['label']);
        $this->assertEquals('Test User', $data[1]['label']);
        $this->assertEquals('Filtered', $data[2]['label']);
    }

    public function test_handle_search_exception_returns_error_response() {
        $request = $this->getMockBuilder('WP_REST_Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('get_param')->with('term')->willReturn('fail');

        $this->postSearchHandler->method('search')->willThrowException(new Exception('fail!'));
        $this->logger->expects($this->once())->method('logError');

        $response = $this->service->handleSearch($request);
        $this->assertEquals(500, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('Search failed', $data['error']);
        $this->assertStringContainsString('fail!', $data['message']);
    }

    public function test_check_permissions_true_when_user_can_edit_posts() {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        $this->assertTrue($this->service->checkPermissions());
    }

    public function test_check_permissions_false_when_user_cannot_edit_posts() {
        $user_id = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);
        $this->assertFalse($this->service->checkPermissions());
    }

    public function test_get_search_stats_returns_expected_array() {
        // Set options for PluginConfig
        update_option('quickal_settings', [
            'post_types' => ['post', 'page', 'custom'],
            'users_search' => true
        ]);
        $stats = $this->service->getSearchStats();
        $this->assertEquals(['post', 'page', 'custom'], $stats['post_types_enabled']);
        $this->assertTrue($stats['user_search_enabled']);
        $this->assertEquals(3, $stats['total_post_types']);
    }
} 