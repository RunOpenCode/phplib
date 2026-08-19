<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Flip operator.
 *
 * Flip operator iterates over given stream of key-value pairs and yields
 * each value-key pair in a single sequence.
 *
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Flip;
 *
 * $flip = new Flip(
 *   source: ['a' => 1, 'b' => 3, 'c' => 5],
 * );
 * // The resulting sequence will be: 1 => 'a', 3 => 'b', 5 => 'c'
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TValue, TKey>
 * @implements OperatorInterface<TValue, TKey>
 */
final class Flip extends AbstractStream implements OperatorInterface
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
        foreach ($this->source as $key => $value) {
            yield $value => $key;
        }
    }
}
