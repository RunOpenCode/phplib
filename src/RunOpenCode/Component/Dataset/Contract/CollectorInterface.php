<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Collector interface.
 *
 * @template TCollectedValue
 */
interface CollectorInterface
{
    /**
     * Collected value.
     *
     * @var TCollectedValue
     */
    public mixed $value {
        get;
    }

    /**
     * List of aggregated values collected during iteration process.
     *
     * @var array<non-empty-string, mixed>
     */
    public array $aggregators {
        get;
    }

    /**
     * Check if collector can be iterated through.
     *
     * Some collectors may be iterated only once, after that they are considered closed. Some
     * collectors may be iterated multiple times and are never considered closed.
     */
    public bool $closed {
        get;
    }
}
