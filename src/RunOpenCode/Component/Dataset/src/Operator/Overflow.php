<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Exception\StreamOverflowException;

/**
 * Overflow operator.
 *
 * Monitors the number of items yielded by the stream and raises an exception when
 * the allowed limit is exceeded.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Overflow;
 *
 * $stream = new Overflow(
 *    source: ['a' => 1, 'b' => 2, 'c' => 3],
 *    capacity: 2,
 *    throw: new \Exception('Max number of items exceeded.'),
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type ThrowableFactoryCallable = callable(StreamOverflowException=): \Throwable
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Overflow extends AbstractStream implements OperatorInterface
{
    private \Closure $throw;

    /**
     * @param iterable<TKey, TValue>                   $source   Collection to iterate over.
     * @param positive-int                             $capacity Maximum number of items to iterate over.
     * @param \Throwable|ThrowableFactoryCallable|null $throw    Exception to throw if stream yielded more items then capacity allows.
     */
    public function __construct(
        private readonly iterable $source,
        private readonly int      $capacity,
        \Throwable|callable|null  $throw = null,
    ) {
        parent::__construct($source);
        $this->throw = match (true) {
            null === $throw => fn(): never => throw new StreamOverflowException($this->capacity),
            $throw instanceof \Throwable => static fn(): never => throw $throw,
            default => fn(): never => throw $throw(new StreamOverflowException($this->capacity)),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $counter = 0;

        foreach ($this->source as $key => $value) {
            if ($counter >= $this->capacity) {
                ($this->throw)();
            }

            yield $key => $value;

            $counter++;
        }
    }
}
