<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Keys operator.
 *
 * Keys operator iterates over given stream of key-value pairs and yields
 * each key in a single sequence.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Keys;
 *
 * $keys = new Keys(
 *   source: ['a' => 1, 'b' => 3, 'c' => 5],
 * );
 * // The resulting sequence will be: 0 => 'a', 1 => 'b', 2 => 'c'
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<int, TKey>
 * @implements OperatorInterface<int, TKey>
 */
final class Keys extends AbstractStream implements OperatorInterface
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
            yield $key;
        }
    }
}
