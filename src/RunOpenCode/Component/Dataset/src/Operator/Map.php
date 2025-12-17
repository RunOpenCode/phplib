<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Map operator.
 *
 * Map operator iterates over given collection and yields transformed items.
 *
 * User must provide a callable to transform each item value. Additionally,
 * user may provide a callable to transform each item key. If key transform
 * callable is not provided, original keys are preserved.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Map;
 *
 * $map = new Map(
 *   collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *   valueTransform: static fn(int $value, string $key): int => $value * 2,
 *   keyTransform: static fn(string $key, int $value): string => \strtoupper($key),
 * );
 * // The resulting sequence will be: 'A' => 2, 'B' => 4, 'C' => 6
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 * @template TModifiedValue
 *
 * @phpstan-type ValueTransformCallable = callable(TValue, TKey=): TModifiedValue
 * @phpstan-type KeyTransformCallable = callable(TKey, TValue=): TModifiedKey
 *
 * @extends AbstractStream<TModifiedKey, TModifiedValue>
 * @implements OperatorInterface<TModifiedKey, TModifiedValue>
 */
final class Map extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $valueTransform;

    private readonly \Closure $keyTransform;

    /**
     * @param iterable<TKey, TValue>    $collection     Collection to iterate over.
     * @param ValueTransformCallable    $valueTransform User defined callable to transform item values.
     * @param KeyTransformCallable|null $keyTransform   User defined callable to transform item keys. If null, original keys are preserved.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $valueTransform,
        ?callable                 $keyTransform = null
    ) {
        parent::__construct($this->collection);
        $this->valueTransform = $valueTransform(...);
        $this->keyTransform   = ($keyTransform ?? static fn($key, $value): mixed => $key)(...);
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(): \Traversable
    {
        foreach ($this->collection as $key => $value) {
            yield ($this->keyTransform)($key, $value) => ($this->valueTransform)($value, $key);
        }
    }
}
