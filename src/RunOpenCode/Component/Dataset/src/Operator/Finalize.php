<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Finalize operator.
 *
 * Finalize operator iterates over given collection and yields all
 * items. When iteration is completed or exception is thrown,
 * it invokes finalization function.
 *
 * Behavior is same as if iteration is within try/finally block.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Finalize;
 *
 * $finalize = new Finalize(
 *    collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
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

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param FinalizerCallable      $finalizer  User defined callable to invoke when iterator is depleted or exception is thrown.
     */
    public function __construct(
        iterable $collection,
        callable $finalizer,
    ) {
        parent::__construct($collection);
        $this->finalizer = $finalizer(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        try {
            yield from $this;
        } finally {
            ($this->finalizer)();
        }
    }
}
