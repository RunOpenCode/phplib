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
 * By default, keys from inner iterables are not preserved, which can be
 * overridden in constructor.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Flatten;
 *
 * $flatten = new Flatten(
 *   collection: new Dataset(['first' => ['a' => 1, 'b' => 3], 'second' => ['c' => 5]]),
 * );
 * // The resulting sequence will be: 0 => 1, 1 => 3, 2 => 5
 * // With `preserveKeys` set to true, resulting sequence would be: 'a' => 1, 'b' => 3, 'c' => 5
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<int|TKey, TValue>
 * @implements OperatorInterface<int|TKey, TValue>
 */
final class Flatten extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<mixed, iterable<TKey, TValue>> $collection   Collection to iterate over.
     * @param bool                                    $preserveKeys Should keys be preserved from the flattened collections, false by default.
     */
    public function __construct(
        private readonly iterable $collection,
        private readonly bool     $preserveKeys = false,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $items) {
            foreach ($items as $key => $value) {
                if ($this->preserveKeys) {
                    yield $key => $value;
                    continue;
                }

                yield $value;
            }
        }
    }
}
