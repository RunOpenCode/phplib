<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Collector\IndexedCollector;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Stream;

/**
 * Left join operator.
 *
 * This operator mimics the SQL LEFT JOIN found in relational databases.
 *
 * It iterates over the source stream and joins each item with values
 * from the joining stream based on strict key equality.
 *
 * The operator yields the key from the source stream and a tuple
 * containing the source value as the first element and an iterable
 * of joined values from the joining stream as the second element.
 *
 * WARNING: This operator is not memory-efficient. Memory
 * consumption depends on the size of the joining stream.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\LeftJoin;
 *
 * $leftJoin = new LeftJoin(
 *   left: [1 => 'a', 2 => 'b', 3 => 'c'],
 *   right: [1 => 'x', 1 => 'y', 2 => 'z'],
 * );
 *
 * // The resulting sequence will be:
 * // 1 => ['a', ['x', 'y']]
 * // 2 => ['b', ['z']]
 * // 3 => ['c', []]
 * ```
 *
 * @template TKey
 * @template TLeftValue
 * @template TRightValue
 *
 * @extends AbstractStream<TKey, array{TLeftValue, iterable<TRightValue>}>
 * @implements OperatorInterface<TKey, array{TLeftValue, iterable<TRightValue>}>
 */
final class LeftJoin extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TLeftValue>  $source Stream source to iterate over on the left side of the left join operation.
     * @param iterable<TKey, TRightValue> $join   Stream source to iterate over on the right side of the left join operation.
     */
    public function __construct(
        private readonly iterable $source,
        private readonly iterable $join
    ) {
        parent::__construct($this->source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $join = new Stream($this->join)->collect(IndexedCollector::class);

        foreach ($this->source as $key => $value) {
            yield $key => [
                $value,
                $join->offsetExists($key) ? $join->offsetGet($key) : [],
            ];
        }
    }
}
