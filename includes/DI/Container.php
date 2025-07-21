<?php
/**
 * Dependency Injection Container
 *
 * @package QuickAL
 * @subpackage DI
 * @since 1.0.0
 */

namespace QUICKAL\DI;

use QUICKAL\Services\LoggerService;
use QUICKAL\Services\PostSearchHandler;
use QUICKAL\Services\UserSearchHandler;
use QUICKAL\Services\SearchService;
use QUICKAL\Services\AdminScriptsService;
use QUICKAL\Services\SettingsService;
use QUICKAL\QuickAL;

class Container
{
    private array $services = [];

    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return object Service instance
     * @throws \Exception If service not found
     */
    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        return $this->createService($id);
    }

    /**
     * Create a service instance
     *
     * @param string $id Service identifier
     * @return object Service instance
     * @throws \Exception If service cannot be created
     */
    private function createService(string $id): object
    {
        switch ($id) {
            case LoggerService::class:
                $service = new LoggerService();
                break;

            case PostSearchHandler::class:
                $service = new PostSearchHandler($this->get(LoggerService::class));
                break;

            case UserSearchHandler::class:
                $service = new UserSearchHandler($this->get(LoggerService::class));
                break;

            case SearchService::class:
                $service = new SearchService(
                    $this->get(PostSearchHandler::class),
                    $this->get(UserSearchHandler::class),
                    $this->get(LoggerService::class)
                );
                break;

            case AdminScriptsService::class:
                $service = new AdminScriptsService($this->get(LoggerService::class));
                break;

            case SettingsService::class:
                $service = new SettingsService($this->get(LoggerService::class));
                break;

            case QuickAL::class:
                $service = new QuickAL(
                    $this->get(SearchService::class),
                    $this->get(AdminScriptsService::class),
                    $this->get(SettingsService::class),
                    $this->get(LoggerService::class)
                );
                break;

            default:
                throw new \Exception("Service '$id' not found in container");
        }

        $this->services[$id] = $service;
        return $service;
    }

    /**
     * Check if a service exists in the container
     *
     * @param string $id Service identifier
     * @return bool True if service exists
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || in_array($id, [
            LoggerService::class,
            PostSearchHandler::class,
            UserSearchHandler::class,
            SearchService::class,
            AdminScriptsService::class,
            SettingsService::class,
            QuickAL::class,
        ]);
    }
} 