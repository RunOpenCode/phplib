<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset;

use RunOpenCode\Component\Dataset\Aggregator\Registry;
use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Prototype for streams.
 *
 * @template TKey
 * @template TValue
 *
 * @implements StreamInterface<TKey, TValue>
 */
abstract class AbstractStream implements StreamInterface
{
    /**
     * {@inheritdoc}
     */
    final public array $upstreams {
        get {
            return $this->upstreams;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public array $aggregators {
        get {
            return $this->registry->aggregators;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public array $aggregated {
        get {
            return \array_map(
                static fn(AggregatorInterface $aggregator): mixed => $aggregator->value,
                $this->registry->aggregators,
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    final public bool $closed = false {
        get {
            return $this->closed;
        }
    }

    private Registry $registry;

    /**
     * @param iterable<mixed, mixed> ...$upstreams
     */
    protected function __construct(iterable ...$upstreams)
    {
        $this->upstreams = \array_values($upstreams);
        $this->registry  = new Registry();

        if ($this instanceof AggregatorInterface) {
            $this->registry->register($this);
        }

        foreach ($upstreams as $upstream) {
            // A root stream source.
            if (!$upstream instanceof StreamInterface) {
                continue;
            }

            if ($upstream instanceof AggregatorInterface) {
                $this->registry->register($upstream);
            }

            foreach ($upstream->aggregators as $aggregator) {
                $this->registry->register($aggregator);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        if ($this->closed) {
            throw new LogicException('You cannot iterate over closed stream.');
        }

        $this->closed = true;

        yield from $this->iterate();
    }

    /**
     * Actual stream iteration logic.
     *
     * @return \Traversable<TKey, TValue>
     */
    abstract protected function iterate(): \Traversable;
}
