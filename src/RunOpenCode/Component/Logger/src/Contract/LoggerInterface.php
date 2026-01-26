<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Logger\Contract;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;

/**
 * Extended logger interface that provides additional methods for exception handling.
 */
interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * Log exception without throwing it.
     *
     * This method may throw exception in other environments (e.g., development, testing)
     * to facilitate debugging, however, in production environments it MUST only log
     * the exception.
     *
     * @template T of \Throwable
     *
     * @param T                     $exception Exception to log.
     * @param non-empty-string|null $message   Optional message to log along with the exception, or null to use exception message.
     * @param mixed[]               $context   Additional context data.
     * @param LogLevel::*|null      $level     If log level is not provided, default log level will be used.
     *
     * @throws T
     */
    public function exception(\Throwable $exception, \Stringable|string|null $message = null, array $context = [], ?string $level = null): void;

    /**
     * Log and throw exception.
     *
     * This method logs exception and then re-throws it.
     *
     * @template T of \Throwable
     *
     * @param T                     $exception Exception to log.
     * @param non-empty-string|null $message   Optional message to log along with the exception, or null to use exception message.
     * @param mixed[]               $context   Additional context data.
     * @param LogLevel::*|null      $level     If log level is not provided, default log level will be used.
     *
     * @throws T
     */
    public function throw(\Throwable $exception, \Stringable|string|null $message = null, array $context = [], ?string $level = null): void;
}
