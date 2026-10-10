<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Pairwise operator.
 *
 * Iterates over two streaming sources in parallel, yielding pairs of keys and values from both sources.
 *
 * If size of both sources is not equal, missing keys and values will be filled with `null` values.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Merge;
 *
 * $merged = new Pairwise(
 *   first: ['a' => 1, 'b' => 2],
 *   second: ['c' => 3, 'd' => 4, 'e' => 5],
 * );
 *
 * // The resulting sequence will be:
 * ['a', 'c'] => [1, 3]
 * ['b', 'd'] => [2, 4]
 * [null, 'e'] => [null, 5]
 * ```
 *
 * @template TKey1
 * @template TValue1
 * @template TKey2
 * @template TValue2
 *
 * @extends AbstractStream<array{TKey1|null,TKey2|null}, array{TValue1|null,TValue2|null}>
 * @implements OperatorInterface<array{TKey1|null,TKey2|null}, array{TValue1|null,TValue2|null}>
 */
final class Pairwise extends AbstractStream implements OperatorInterface
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
        $firstIterator  = \is_array($this->first) ? new \ArrayIterator($this->first) : new \IteratorIterator($this->first);
        $secondIterator = \is_array($this->second) ? new \ArrayIterator($this->second) : new \IteratorIterator($this->second);

        while ($firstIterator->valid() || $secondIterator->valid()) {
            $key1   = $firstIterator->valid() ? $firstIterator->key() : null;
            $key2   = $secondIterator->valid() ? $secondIterator->key() : null;
            $value1 = $firstIterator->valid() ? $firstIterator->current() : null;
            $value2 = $secondIterator->valid() ? $secondIterator->current() : null;

            yield [$key1, $key2] => [$value1, $value2];

            if ($firstIterator->valid()) {
                $firstIterator->next();
            }

            if ($secondIterator->valid()) {
                $secondIterator->next();
            }
        }
    }
}
