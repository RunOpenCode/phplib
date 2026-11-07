<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

use function RunOpenCode\Component\Dataset\to_iterator;

/**
 * ZipShortest operator.
 *
 * Iterates over two streaming sources in parallel, yielding pairs of keys and values from both sources.
 *
 * Iteration stops when the shortest source is exhausted, meaning that if one source has fewer
 * elements than the other, the iteration will end as soon as the shorter source runs out of elements.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\ZipShortest;
 *
 * $merged = new ZipShortest(
 *   first: ['a' => 1, 'b' => 2],
 *   second: ['c' => 3, 'd' => 4, 'e' => 5],
 * );
 *
 * // The resulting sequence will be:
 * ['a', 'c'] => [1, 3]
 * ['b', 'd'] => [2, 4]
 * ```
 *
 * @template TKey1
 * @template TValue1
 * @template TKey2
 * @template TValue2
 *
 * @extends AbstractStream<array{TKey1,TKey2}, array{TValue1,TValue2}>
 * @implements OperatorInterface<array{TKey1,TKey2}, array{TValue1,TValue2}>
 */
final class ZipShortest extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey1,TValue1> $first  First source to iterate over.
     * @param iterable<TKey2,TValue2> $second Second source to iterate over.
     */
    public function __construct(
        private readonly iterable $first,
        private readonly iterable $second,
    ) {
        parent::__construct($this->first);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $firstIterator  = to_iterator($this->first);
        $secondIterator = to_iterator($this->second);

        while ($firstIterator->valid() && $secondIterator->valid()) {
            $key1   = $firstIterator->key();
            $key2   = $secondIterator->key();
            $value1 = $firstIterator->current();
            $value2 = $secondIterator->current();

            yield [$key1, $key2] => [$value1, $value2];

            $firstIterator->next();
            $secondIterator->next();
        }
    }
}
