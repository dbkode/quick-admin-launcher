<?php
/**
 * Logger Service
 *
 * @package QuickAL
 * @subpackage Services
 * @since 1.0.0
 */

namespace QUICKAL\Services;

class LoggerService
{
    private const LOG_PREFIX = '[QuickAL]';

    /**
     * Log a message with specified level
     *
     * @param string $message Message to log
     * @param string $level Log level (debug, info, warning, error)
     * @return void
     */
    public function log(string $message, string $level = 'info'): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $formattedMessage = sprintf('%s %s: %s', self::LOG_PREFIX, strtoupper($level), $message);
            error_log($formattedMessage);
        }
    }

    /**
     * Log an error with exception details
     *
     * @param \Throwable $exception Exception to log
     * @return void
     */
    public function logError(\Throwable $exception): void
    {
        $message = sprintf(
            'Exception: %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->log($message, 'error');
    }

    /**
     * Log debug information
     *
     * @param string $message Debug message
     * @param array $context Additional context data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        if (!empty($context)) {
            $message .= ' Context: ' . json_encode($context);
        }
        $this->log($message, 'debug');
    }

    /**
     * Log warning message
     *
     * @param string $message Warning message
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log($message, 'warning');
    }
} 