<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Aggregator interface.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @extends StreamInterface<TKey, TValue>
 */
interface AggregatorInterface extends StreamInterface
{
    /**
     * Get name of the aggregation.
     *
     * @var non-empty-string
     */
    public string $name {
        get;
    }

    /**
     * Get value of the aggregation.
     *
     * @return TReducedValue Reduced value.
     */
    public mixed $value {
        get;
    }
}
