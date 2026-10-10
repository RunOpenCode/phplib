<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * IndexBy operator.
 *
 * IndexBy operator iterates over given stream source and applies
 * callable function to generate new keys for the resulting stream.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\IndexBy;
 *
 * $map = new IndexBy(
 *   source: ['a' => 1, 'b' => 2, 'c' => 3],
 *   callable: static fn(int $value, string $key): int => $value * 2,
 * );
 * // The resulting sequence will be: 2 => 1, 4 => 2, 6 => 3
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 *
 * @phpstan-type KeyGeneratorCallable = callable(TValue, TKey): TModifiedKey
 *
 * @extends AbstractStream<TModifiedKey, TValue>
 * @implements OperatorInterface<TModifiedKey, TValue>
 */
final class IndexBy extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $callable;

    /**
     * @param iterable<TKey, TValue> $source   Stream source to iterate over.
     * @param KeyGeneratorCallable   $callable Callable function to generate new keys for the resulting stream.
     */
    public function __construct(
        private readonly iterable $source,
        callable                  $callable,
    ) {
        parent::__construct($this->source);
        $this->callable = $callable(...);
    }

    /**
     * @inheritdoc
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $key => $value) {
            yield ($this->callable)($value, $key) => $value;
        }
    }
}
