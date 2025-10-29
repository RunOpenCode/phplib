<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Reverse operator.
 *
 * Reverse operator iterates over given collection and yields items in reverse order.
 *
 * WARNING: this is not memory efficient operator.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Reverse;
 *
 * $reverse = new Reverse(
 *  collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 * );
 * // The resulting sequence will be: 'c' => 3, 'b' => 2, 'a' => 1
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Reverse extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over in reverse order.
     */
    public function __construct(
        private readonly iterable $collection,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $buffer = [];

        foreach ($this->collection as $key => $value) {
            $buffer[] = [$key, $value];
        }

        for ($i = \count($buffer) - 1; $i >= 0; $i--) {
            yield $buffer[$i][0] => $buffer[$i][1];
        }
    }
}
