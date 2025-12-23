<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Take operator.
 *
 * Take operator iterates over given stream source and yields only the first N items.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Take;
 *
 * $take = new Take(
 *   source: ['a' => 1, 'b' => 2, 'c' => 3],
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
     * @param iterable<TKey, TValue> $source Stream source to iterate over.
     * @param positive-int           $count  Number of items to yield.
     */
    public function __construct(
        private readonly iterable $source,
        private readonly int      $count,
    ) {
        parent::__construct($this->source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $count = 0;

        foreach ($this->source as $key => $value) {
            $count++;

            if ($count > $this->count) {
                break;
            }

            yield $key => $value;
        }
    }
}
