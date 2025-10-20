<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Interface for dataset reducers.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface ReducerInterface extends \IteratorAggregate
{
    /**
     * Get reduced value.
     *
     * @var TReducedValue
     */
    public mixed $value {
        get;
    }
}
