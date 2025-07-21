<?php
use PHPUnit\Framework\MockObject\MockObject;
use QUICKAL\Services\SettingsService;
use QUICKAL\Services\LoggerService;

/**
 * @group quickal
 */
class SettingsServiceTest extends WP_UnitTestCase {
    /** @var MockObject */
    private $logger;
    /** @var SettingsService */
    private $service;

    public function setUp(): void {
        parent::setUp();
        $this->logger = $this->createMock(LoggerService::class);
        $this->service = new SettingsService($this->logger);
    }

    public function test_sanitize_settings_with_valid_input() {
        $input = [
            'post_types' => ['post', 'page', 'custom'],
            'users_search' => '1',
            'hotkey' => [
                'key' => 'k',
                'alt' => '1',
                'ctrl' => '1',
                'shift' => '',
                'meta' => '',
            ]
        ];
        $this->logger->expects($this->once())->method('debug');
        $sanitized = $this->service->sanitizeSettings($input);
        $this->assertEquals(['post', 'page', 'custom'], $sanitized['post_types']);
        $this->assertTrue($sanitized['users_search']);
        $this->assertEquals([
            'key' => 'k',
            'alt' => true,
            'ctrl' => true,
            'shift' => false,
            'meta' => false,
        ], $sanitized['hotkey']);
    }

    public function test_sanitize_settings_with_missing_fields() {
        $input = [];
        $this->logger->expects($this->once())->method('debug');
        $sanitized = $this->service->sanitizeSettings($input);
        $this->assertArrayNotHasKey('post_types', $sanitized);
        $this->assertFalse($sanitized['users_search']);
        $this->assertArrayNotHasKey('hotkey', $sanitized);
    }

    public function test_sanitize_settings_with_partial_hotkey() {
        $input = [
            'hotkey' => [
                'key' => 'x',
                'ctrl' => '1',
            ]
        ];
        $this->logger->expects($this->once())->method('debug');
        $sanitized = $this->service->sanitizeSettings($input);
        $this->assertEquals([
            'key' => 'x',
            'alt' => false,
            'ctrl' => true,
            'shift' => false,
            'meta' => false,
        ], $sanitized['hotkey']);
    }

    public function test_add_settings_link_appends_link() {
        $links = ['<a href="#">Existing</a>'];
        $result = $this->service->addSettingsLink($links);
        $this->assertCount(2, $result);
        $this->assertStringContainsString('options-general.php?page=quickal-settings', $result[1]);
    }
} 