<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Exception\StreamEmptyException;

/**
 * IfEmpty operator.
 *
 * IfEmpty operator tracks the number of yielded items. If the stream is empty, it
 * will invoke the provided callable and yield from it as an alternative source of
 * data.
 *
 * If exception is provided instead of alternative source of data, that exception
 * will be thrown.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\IfEmpty;
 *
 * $stream = new IfEmpty(
 *    source: [],
 *    action: static fn(): iterable => ['a' => 1, 'b' => 2, 'c' => 3],
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type FallbackSourceCallable = callable(): iterable<TKey, TValue>
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class IfEmpty extends AbstractStream implements OperatorInterface
{
    private \Closure $fallback;

    /**
     * @param iterable<TKey, TValue>                 $source   Stream source to iterate over.
     * @param FallbackSourceCallable|\Throwable|null $fallback Fallback stream source, or exception to throw.
     */
    public function __construct(
        private readonly iterable $source,
        callable|\Throwable|null  $fallback,
    ) {
        parent::__construct($source);
        $fallback       = $fallback ?? new StreamEmptyException();
        $this->fallback = $fallback instanceof \Throwable ? static fn(): never => throw $fallback : $fallback(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $counter = 0;

        foreach ($this->source as $key => $value) {
            yield $key => $value;
            $counter++;
        }

        if (0 === $counter) {
            yield from ($this->fallback)();
        }
    }
}
