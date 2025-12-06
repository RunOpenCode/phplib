<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param string|string[] $expected
     * @psalm-suppress UnsafeInstantiation
     */
    public static function type(string|array $expected, mixed $actual, ?\Throwable $previous = null): self
    {
        if (!\is_array($expected)) {
            $expected = [$expected];
        }

        $message = \sprintf(
            'Expected "%s", got "%s".',
            \implode('", "', $expected),
            \get_debug_type($actual)
        );

        /** @phpstan-ignore-next-line  */
        return new static($message, $previous);
    }
}
