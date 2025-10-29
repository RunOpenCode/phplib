<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Merge operator.
 *
 * Merge operator iterates over two given collections and yields all items from both collections.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Merge;
 *
 * $merged = new Merge(
 *   first: new Dataset(['a' => 1, 'b' => 2]),
 *   second: new Dataset(['c' => 3, 'd' => 4]),
 * );
 *
 * // The resulting sequence will be: 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4
 * ```
 *
 * @template TKey1
 * @template TValue1
 * @template TKey2
 * @template TValue2
 *
 * @extends AbstractStream<TKey1|TKey2, TValue1|TValue2>
 * @implements OperatorInterface<TKey1|TKey2, TValue1|TValue2>
 */
final class Merge extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey1, TValue1> $first  First collection to iterate over.
     * @param iterable<TKey2, TValue2> $second Second collection to iterate over.
     */
    public function __construct(
        private readonly iterable $first,
        private readonly iterable $second
    ) {
        parent::__construct($this->first, $this->second);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        yield from $this->first;
        yield from $this->second;
    }
}
