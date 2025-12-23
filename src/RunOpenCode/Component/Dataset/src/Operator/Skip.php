<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Skip operator.
 *
 * The skip operator processes a stream source by discarding the first N items
 * and yielding all subsequent items.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Skip;
 *
 * $skip = new Skip(
 *  stream: ['a' => 1, 'b' => 2, 'c' => 3],
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
     * @param iterable<TKey, TValue> $source Stream source to iterate over.
     * @param positive-int           $count  Number of items to skip.
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

            if ($count <= $this->count) {
                continue;
            }

            yield $key => $value;
        }
    }
}
