<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Flatten operator.
 *
 * Flatten operator iterates over given collection of iterables and yields
 * each item from each iterable in a single flat sequence.
 *
 * Keys from inner iterables are not preserved, new keys are generated
 * in a continuous manner starting from 0.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Flatten;
 *
 * $flatten = new Flatten(
 *   collection: new Dataset(['a' => [1, 2], 'b' => [3, 4], 'c' => [5]]),
 * );
 * // The resulting sequence will be: 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5
 * ```
 *
 * @template TValue
 *
 * @extends AbstractStream<int, TValue>
 * @implements OperatorInterface<int, TValue>
 */
final class Flatten extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<mixed, iterable<TValue>> $collection Collection to iterate over.
     */
    public function __construct(
        private readonly iterable $collection,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $items) {
            foreach ($items as $value) {
                yield $value;
            }
        }
    }
}
