<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Logger\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RunOpenCode\Component\Logger\Logger;

final class LoggerTest extends TestCase
{
    private LoggerInterface&MockObject $decorated;

    protected function setUp(): void
    {
        parent::setUp();
        $this->decorated = $this->createMock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->decorated);
    }

    /**
     * @param non-empty-string      $exceptionMessage
     * @param non-empty-string|null $loggerMessage
     * @param LogLevel::*|null      $logLevel
     */
    #[Test]
    #[DataProvider('get_data_for_exception')]
    public function exception(
        string  $exceptionMessage,
        ?string $loggerMessage,
        ?string $logLevel,
        string  $expectedMessage,
    ): void {
        $exception = new \RuntimeException($exceptionMessage);

        $this
            ->decorated
            ->expects($this->once())
            ->method('log')
            ->with(
                $logLevel ?? LogLevel::CRITICAL,
                $this->stringContains($expectedMessage),
                ['exception' => $exception]
            );

        new Logger($this->decorated)->exception($exception, $loggerMessage, [], $logLevel);
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string|null, LogLevel::*|null, string}>
     */
    public static function get_data_for_exception(): iterable
    {
        yield 'It logs exception with default message and default log level.' => ['Test exception', null, null, 'Test exception'];
        yield 'It logs exception with custom message and default log level.' => ['Test exception', 'Custom message', null, 'Custom message'];
        yield 'It logs exception with custom message and custom alert level.' => ['Test exception', 'Custom message', LogLevel::ALERT, 'Custom message'];
    }

    #[Test]
    public function exception_with_context(): void
    {
        $exception = new \RuntimeException('Test exception');
        $this
            ->decorated
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::CRITICAL,
                'Test exception',
                ['foo' => 'bar', 'exception' => $exception],
            );

        new Logger($this->decorated)->exception($exception, null, ['foo' => 'bar']);
    }

    #[Test]
    public function exception_throws_in_debug_mode(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        $exception = new \RuntimeException('Test exception');

        $this
            ->decorated
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::CRITICAL,
                'Test exception',
                ['exception' => $exception]
            );

        new Logger($this->decorated, [], true)->exception($exception);
    }

    #[Test]
    public function throw(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        $exception = new \RuntimeException('Test exception');

        $this
            ->decorated
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::CRITICAL,
                'Test exception',
                ['exception' => $exception]
            );

        new Logger($this->decorated, [], true)->throw($exception);
    }

    /**
     * @param LogLevel::* $method
     */
    #[Test]
    #[DataProvider('get_data_for_level_methods')]
    public function level_methods(string $method): void
    {
        $this
            ->decorated
            ->expects($this->once())
            ->method('log')
            ->with($method, \sprintf('Message for "%s".', $method), []);

        new Logger($this->decorated)->{$method}(\sprintf('Message for "%s".', $method));
    }

    /**
     * @return iterable<string, array{LogLevel::*}>
     */
    public static function get_data_for_level_methods(): iterable
    {
        /** @var list<LogLevel::*> $levels */
        $levels = new \ReflectionClass(LogLevel::class)->getConstants();

        foreach ($levels as $level) {
            yield \sprintf('Method %s()', $level) => [$level];
        }
    }

    #[Test]
    public function log(): void
    {
        $this->decorated->expects($this->once())
                        ->method('log')
                        ->with(LogLevel::INFO, 'Log message');

        $logger = new Logger($this->decorated);

        $logger->log(LogLevel::INFO, 'Log message');
    }
}
