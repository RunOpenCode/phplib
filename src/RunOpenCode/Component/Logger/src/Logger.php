<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Logger;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use RunOpenCode\Component\Logger\Contract\LoggerContextInterface;
use RunOpenCode\Component\Logger\Contract\LoggerInterface;

/**
 * Default logger implementation.
 */
final readonly class Logger implements LoggerInterface
{
    /**
     * Create instance of logger.
     *
     * @param LogLevel::*                      $defaultLevel     Default log level to use when logging exceptions.
     * @param iterable<LoggerContextInterface> $contextProviders Context providers to enrich log context.
     */
    public function __construct(
        private PsrLoggerInterface $decorated,
        private iterable           $contextProviders = [],
        private bool               $debug = false,
        private string             $defaultLevel = LogLevel::CRITICAL
    ) {
        if (!$this->isValidLogLevel($defaultLevel)) {
            throw new \InvalidArgumentException(\sprintf(
                'Provided value "%s" for default log level is not known (known values are "%s").',
                $defaultLevel,
                \implode('", "', self::getLogLevels())
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exception(\Throwable $exception, \Stringable|string|null $message = null, array $context = [], ?string $level = null): void
    {
        $this->logException($exception, $message, $context, $level);

        if ($this->debug) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function throw(\Throwable $exception, \Stringable|string|null $message = null, array $context = [], ?string $level = null): void
    {
        $this->logException($exception, $message, $context, $level);

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $collected = [];

        foreach ($this->contextProviders as $provider) {
            $collected[] = $provider->get($context);
        }

        $this->decorated->log($level, $message, \array_merge($context, ...$collected));
    }

    /**
     * Get known log levels defined by psr/log.
     *
     * @return list<non-empty-string>
     */
    public static function getLogLevels(): array
    {
        /** @var list<non-empty-string>|null $levels */
        static $levels;

        if (!isset($levels)) {
            /** @var list<non-empty-string> $levels */
            $levels = \array_values(new \ReflectionClass(LogLevel::class)->getConstants(\ReflectionClassConstant::IS_PUBLIC));
        }

        return $levels;
    }

    /**
     * @param mixed[] $context
     *
     * @throws \Throwable
     */
    private function logException(\Throwable $exception, \Stringable|string|null $message = null, array $context = [], ?string $level = null): void
    {
        $message = $message ? (string)$message : $exception->getMessage();
        $level   = $level ?? $this->defaultLevel;

        if (!$this->isValidLogLevel($level)) {
            throw new \InvalidArgumentException(\sprintf(
                'Provided value "%s" for log level is not in known (known values are "%s").',
                $level,
                \implode('", "', self::getLogLevels())
            ));
        }

        $this->{$level}($message, \array_merge(
            $context,
            ['exception' => $exception]
        ));
    }

    /**
     * Check if provided log level is defined by psr/log.
     */
    private function isValidLogLevel(string $level): bool
    {
        /** @var array<non-empty-string, non-empty-string>|null $levels */
        static $levels;

        if (!isset($levels)) {
            /** @var array<non-empty-string, non-empty-string> $levels */
            $levels = \array_combine(self::getLogLevels(), self::getLogLevels());
        }

        return isset($levels[$level]);
    }
}
