<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Represents a feed forward data stream.
 *
 * @template TKey
 * @template TValue
 *
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface StreamInterface extends \IteratorAggregate
{
    /**
     * Get list of stream data origins.
     *
     * @var list<iterable<mixed, mixed>>
     */
    public array $upstreams {
        get;
    }

    /**
     * Get list of aggregators attached to this stream.
     *
     * @var array<non-empty-string, AggregatorInterface<mixed, mixed, mixed>>
     */
    public array $aggregators {
        get;
    }

    /**
     * Get aggregated values collected during iteration process.
     *
     * @var array<non-empty-string, mixed>
     */
    public array $aggregated {
        get;
    }

    /**
     * Check if stream has been iterated.
     *
     * Do note that this denotes only if iteration started, not
     * if stream has been fully iterated.
     *
     * This information may be used to determine if stream can
     * be iterated or not as implementation assumes that all
     * streams can not be rewound.
     */
    public bool $closed {
        get;
    }
}
