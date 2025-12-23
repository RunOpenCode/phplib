<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Flatten operator.
 *
 * Flatten operator iterates over given stream of iterables and yields
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
 *   source: ['first' => ['a' => 1, 'b' => 3], 'second' => ['c' => 5]],
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
     * @param iterable<mixed, iterable<TKey, TValue>> $source       Stream of streams to iterate over.
     * @param bool                                    $preserveKeys Should keys be preserved from the flattened stream, false by default.
     */
    public function __construct(
        private readonly iterable $source,
        private readonly bool     $preserveKeys = false,
    ) {
        parent::__construct($this->source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $items) {
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
