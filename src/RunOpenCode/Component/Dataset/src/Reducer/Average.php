<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Reducer which calculates average of values from a stream of values.
 *
 * Null values are ignored. You may define if null values are included in
 * the count of items when calculating average.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type ExtractorCallable = callable(TValue, TKey): (int|float|null)
 *
 * @implements ReducerInterface<TKey, TValue, float|null>
 */
final class Average implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => 0 !== $this->count && null !== $this->total ? $this->total / $this->count : null;
    }

    /**
     * Extractor to extract reducible value.
     */
    private readonly \Closure $extractor;

    /**
     * Current total of aggregated values.
     */
    private float|null $total;

    /**
     * Current count of aggregated values.
     */
    private int $count = 0;

    /**
     * Create new average reducer.
     *
     * @param int|float|null         $initial   Initial value to start with.
     * @param ExtractorCallable|null $extractor Optional function to extract reducible value.
     * @param bool                   $countNull Should `null` values be accounted for, `false` by default.
     */
    public function __construct(
        int|float|null        $initial = null,
        ?callable             $extractor = null,
        private readonly bool $countNull = false,
    ) {
        $this->extractor = null !== $extractor ? $extractor(...) : static fn(mixed $value): mixed => $value;
        $this->total     = null !== $initial ? (float)$initial : null;
    }

    /**
     * {@inheritdoc}
     */
    public function next(mixed $value, mixed $key): void
    {
        /** @var int|float|null $value */
        $value       = ($this->extractor)($value, $key);
        $this->count += null !== $value || $this->countNull ? 1 : 0;

        if (null === $value) {
            return;
        }

        $this->total = ($this->total ?? 0.0) + (float)$value;
    }
}
