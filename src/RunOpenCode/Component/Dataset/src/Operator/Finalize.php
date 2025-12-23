<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Finalize operator.
 *
 * Iterates over the given stream source and yields its items. When iteration
 * completes or an exception occurs, the finalization function is invoked.
 *
 * This is equivalent to executing the iteration inside a try/finally block.
 *
 * If iteration of the stream source ends prematurely (for example, via a `break`
 * statement), the finalization function is invoked when the operator instance
 * is garbage-collected.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Finalize;
 *
 * $finalize = new Finalize(
 *    source: ['a' => 1, 'b' => 2, 'c' => 3],
 *    finalizer: static fn(): void => // finalization logic,
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type FinalizerCallable = callable(): void
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Finalize extends AbstractStream implements OperatorInterface
{
    private \Closure $finalizer;

    private bool $finalized = false;

    /**
     * @param iterable<TKey, TValue> $source    Stream source to iterate over.
     * @param FinalizerCallable      $finalizer User defined callable to invoke when iterator is depleted, or exception
     *                                          is thrown, or operator instance is garbage collected.
     */
    public function __construct(
        iterable $source,
        callable $finalizer,
    ) {
        parent::__construct($source);
        $this->finalizer = $finalizer(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->finalized = true;

        try {
            yield from $this;
        } finally {
            ($this->finalizer)();
        }
    }

    /**
     * Ensure finalization logic is executed.
     */
    public function __destruct()
    {
        if ($this->finalized) {
            return;
        }

        $this->finalized = true;
        ($this->finalizer)();
    }
}
