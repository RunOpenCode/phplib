<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Reducer which uses custom callback function to reduce items to value.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue = mixed
 *
 * @phpstan-type ReducerCallable = callable(TReducedValue, TValue, TKey=): TReducedValue
 *
 * @implements ReducerInterface<TKey, TValue, TReducedValue>
 */
final class Callback implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value;

    /**
     * Reducer function to apply.
     */
    private readonly \Closure $callback;

    /**
     * Create new callback reducer.
     *
     * @param ReducerCallable $callback Callback function used to reduce values.
     * @param mixed           $initial  Initial value.
     */
    public function __construct(
        callable $callback,
        mixed    $initial = null
    ) {
        $this->callback = $callback(...);
        $this->value    = $initial;
    }

    /**
     * {@inheritdoc}
     */
    public function next(mixed $value, mixed $key): void
    {
        $this->value = ($this->callback)($this->value, $value, $key);
    }
}
