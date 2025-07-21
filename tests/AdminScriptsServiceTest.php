<?php
use PHPUnit\Framework\MockObject\MockObject;
use QUICKAL\Services\AdminScriptsService;
use QUICKAL\Services\LoggerService;

/**
 * @group quickal
 */
class AdminScriptsServiceTest extends WP_UnitTestCase {
    /** @var MockObject */
    private $logger;
    /** @var AdminScriptsService */
    private $service;

    public function setUp(): void {
        parent::setUp();
        $this->logger = $this->createMock(LoggerService::class);
        $this->service = new AdminScriptsService($this->logger);
    }

    public function test_defer_parsing_of_js_for_quickal_handle() {
        $tag = '<script src="foo.js"></script>';
        $handle = 'quickal-react';
        $result = $this->service->deferParsingOfJs($tag, $handle);
        $this->assertStringContainsString('defer src', $result);
    }

    public function test_defer_parsing_of_js_for_non_quickal_handle() {
        $tag = '<script src="bar.js"></script>';
        $handle = 'other-script';
        $result = $this->service->deferParsingOfJs($tag, $handle);
        $this->assertEquals($tag, $result);
    }
} 