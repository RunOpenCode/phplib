<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Take operator.
 *
 * Take operator iterates over given collection and yields only the first N items,
 * where N is defined by user.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Take;
 *
 * $take = new Take(
 *   collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *   count: 2,
 * );
 * // $take will yield ['a' => 1, 'b' => 2]
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Take extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param positive-int           $count      Number of items to yield.
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

            if ($count > $this->count) {
                break;
            }

            yield $key => $value;
        }
    }
}
