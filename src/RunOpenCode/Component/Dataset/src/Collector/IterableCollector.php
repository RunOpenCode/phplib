<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Collect as original iterable.
 *
 * Allows you to iterate through whole dataset providing you the access to
 * aggregators when collection is iterated.
 *
 * @template TKey
 * @template TValue
 *
 * @implements CollectorInterface<iterable<TKey, TValue>>
 * @implements \IteratorAggregate<TKey, TValue>
 */
final class IterableCollector implements \IteratorAggregate, CollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get {
            return $this->getIterator();
        }
    }

    /**
     * {@inheritdoc}
     */
    public array $aggregated {
        get {
            if (!$this->closed) {
                throw new LogicException('Collector must be iterated first.');
            }

            return $this->collection instanceof StreamInterface ? $this->collection->aggregated : [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public private(set) bool $closed = false;

    /**
     * Provides you with total number of iterated elements.
     *
     * @var non-negative-int
     */
    public private(set) int $count = 0;

    /**
     * @param iterable<TKey, TValue> $collection Collection to collect.
     */
    public function __construct(
        private readonly iterable $collection,
        public readonly int       $offset = 0,
        public readonly ?int      $limit = null,
    ) {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        $this->closed = true;

        foreach ($this->collection as $key => $value) {
            yield $key => $value;
            $this->count++;
        }
    }
}
