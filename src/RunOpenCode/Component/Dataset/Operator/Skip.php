<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Skip operator.
 *
 * Skip operator iterates over given collection and skips the first N items,
 * where N is defined by user, yielding the rest of the items.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Skip;
 *
 * $skip = new Skip(
 *  collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *  count: 2,
 * );
 *
 * // The resulting sequence will be: 'c' => 3
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Skip extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param positive-int           $count      Number of items to skip.
     */
    public function __construct(
        private readonly iterable $collection,
        private readonly int      $count,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $count = 0;

        foreach ($this->collection as $key => $value) {
            $count++;

            if ($count <= $this->count) {
                continue;
            }

            yield $key => $value;
        }
    }
}
