<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Pairwise operator.
 *
 * Iterates over streaming source and yields pairs of previous and current key and previous
 * and current value from the source.
 *
 * If iterated source has less than two elements, no elements will be yielded.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Pairwise;
 *
 * $merged = new Pairwise(
 *   source: ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
 * );
 *
 * // The resulting sequence will be:
 * ['a', 'b'] => [1, 2]
 * ['b', 'c'] => [2, 3]
 * ['c', 'd'] => [3, 4]
 * ['d', 'e'] => [4, 5]
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<array{TKey,TKey}, array{TValue,TValue}>
 * @implements OperatorInterface<array{TKey,TKey}, array{TValue,TValue}>
 */
final class Pairwise extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey,TValue> $source Source to iterate over.
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
        $previousKey   = null;
        $previousValue = null;
        $first         = true;

        foreach ($this->source as $key => $value) {
            if ($first) {
                $first         = false;
                $previousKey   = $key;
                $previousValue = $value;

                continue;
            }

            yield [$previousKey, $key] => [$previousValue, $value];

            $previousKey   = $key;
            $previousValue = $value;
        }

        unset($previousKey, $previousValue);
    }
}
