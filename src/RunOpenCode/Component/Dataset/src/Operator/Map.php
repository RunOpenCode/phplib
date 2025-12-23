<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Map operator.
 *
 * Map operator iterates over given stream source and applies transformation
 * functions one keys/values before yielding.
 *
 * Operator may be used to transform only keys, or only values, or both.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Map;
 *
 * $map = new Map(
 *   source: ['a' => 1, 'b' => 2, 'c' => 3],
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
     * @param iterable<TKey, TValue>      $source         Stream source to iterate over.
     * @param ValueTransformCallable|null $valueTransform Optional transformation function for transforming values.
     * @param KeyTransformCallable|null   $keyTransform   Optional transformation function for transforming keys.
     */
    public function __construct(
        private readonly iterable $source,
        ?callable                 $valueTransform = null,
        ?callable                 $keyTransform = null
    ) {
        if (null === $valueTransform && null === $keyTransform) {
            throw new LogicException('At least one transformation function must be provided, either for keys or for values.');
        }

        parent::__construct($this->source);
        $this->valueTransform = ($valueTransform ?? static fn(mixed $value, mixed $key): mixed => $value)(...);
        $this->keyTransform   = ($keyTransform ?? static fn(mixed $key, mixed $value): mixed => $key)(...);
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(): \Traversable
    {
        foreach ($this->source as $key => $value) {
            yield ($this->keyTransform)($key, $value) => ($this->valueTransform)($value, $key);
        }
    }
}
