<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Reducer which counts number of items.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type FilterCallable = callable(TValue, TKey): bool
 *
 * @implements ReducerInterface<TKey, TValue, int>
 */
final class Count implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value = 0;

    /**
     * Filter callable.
     */
    private readonly \Closure $filter;

    /**
     * Create new count reducer.
     *
     * @param FilterCallable|null $filter Optional filter callback to count only items that match the filter.
     */
    public function __construct(
        ?callable $filter = null,
    ) {
        $this->filter = $filter ? $filter(...) : static fn(): bool => true;
    }

    /**
     * {@inheritdoc}
     */
    public function next(mixed $value, mixed $key): void
    {
        if (false === ($this->filter)($value, $key)) {
            return;
        }

        $this->value++;
    }
}
