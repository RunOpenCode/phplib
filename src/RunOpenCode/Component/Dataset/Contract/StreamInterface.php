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
     * Check if stream has been iterated through.
     */
    public bool $closed {
        get;
    }
}
