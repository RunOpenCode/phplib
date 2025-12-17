<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Interface for dataset reducers.
 *
 * Each reducer is a simple, stateful class instance which does
 * data reduction. For each iteration, aggregates the value and
 * stores it into value property.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 */
interface ReducerInterface
{
    /**
     * Reduced value.
     *
     * @var TReducedValue
     */
    public mixed $value {
        get;
    }

    /**
     * Provide key and value from next iteration for reduction.
     *
     * @param TValue        $value Value from current iteration.
     * @param TKey          $key   Key from current iteration.
     */
    public function next(mixed $value, mixed $key): void;
}
