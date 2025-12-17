<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Reducer which calculates maximum value from a collection of values.
 *
 * Null values are ignored.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @phpstan-type ExtractorCallable = callable(TValue, TKey): TReducedValue|null
 * @phpstan-type ComparatorCallable = callable(TReducedValue, TReducedValue): (0|1|-1)
 *
 * @implements ReducerInterface<TKey, TValue, TReducedValue|null>
 */
final class Max implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value;

    private \Closure $extractor;

    private \Closure $comparator;

    /**
     * Create new max reducer.
     *
     * @param TReducedValue|null      $initial    Initial value to start with.
     * @param ExtractorCallable|null  $extractor  Optional reducible value extractor.
     * @param ComparatorCallable|null $comparator Optional comparator.
     */
    public function __construct(
        mixed     $initial = null,
        ?callable $extractor = null,
        ?callable $comparator = null,
    ) {
        $this->value      = $initial;
        $this->extractor  = null !== $extractor ? $extractor(...) : static fn(mixed $value): mixed => $value;
        $this->comparator = null !== $comparator ? $comparator(...) : static fn(mixed $first, mixed $second): int => $first <=> $second;
    }

    /**
     * {@inheritdoc}
     */
    public function next(mixed $value, mixed $key): void
    {
        $value = ($this->extractor)($value, $key);

        if (null === $value) {
            return;
        }

        if (null === $this->value) {
            $this->value = $value;
            return;
        }

        $this->value = 1 === ($this->comparator)($value, $this->value) ? $value : $this->value;
    }
}
