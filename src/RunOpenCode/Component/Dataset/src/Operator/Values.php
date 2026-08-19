<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Values operator.
 *
 * Values operator iterates over given stream of key-value pairs and yields
 * each value in a single sequence.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Values;
 *
 * $values = new Values(
 *   source: ['a' => 1, 'b' => 3, 'c' => 5],
 * );
 * // The resulting sequence will be: 0 => 1, 1 => 3, 2 => 5
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<int, TValue>
 * @implements OperatorInterface<int, TValue>
 */
final class Values extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $source Stream of streams to iterate over.
     */
    public function __construct(
        private readonly iterable $source,
    ) {
        parent::__construct($this->source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $value) {
            yield $value;
        }
    }
}
