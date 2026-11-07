<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Collect iterable into cursored collector.
 *
 * Cursored collector is a read-only collection that allows to iterate over its
 * items only once. It also provides information about previous and next offsets
 * based on provided offset and limit.
 *
 * However, next offset can be determined only after the collection is fully iterated.
 *
 * @template TKey
 * @template TValue
 *
 * @implements CollectorInterface<iterable<TKey, TValue>>
 * @implements \IteratorAggregate<TKey, TValue>
 */
final class CursoredCollector implements \IteratorAggregate, CollectorInterface
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

            return $this->aggregated;
        }
    }

    /**
     * {@inheritdoc}
     */
    public private(set) bool $closed = false;

    /**
     * Get previous offset, if exists.
     */
    public ?int $previous {
        get {
            if ($this->offset <= 0) {
                return null;
            }

            return null === $this->limit ? 0 : \max(0, $this->offset - $this->limit);
        }
    }

    /**
     * Get next offset, if exists.
     */
    public ?int $next {
        get {
            if (!$this->exhausted) {
                throw new LogicException('Collector must be fully iterated first.');
            }

            if (null === $this->limit) {
                return null;
            }

            if (!$this->hasMore) {
                return null;
            }

            return $this->offset + $this->limit;
        }
    }

    /**
     * Indicates whether there are more items available after current collection is fully iterated.
     */
    public bool $hasMore {
        get {
            if (!$this->exhausted) {
                throw new LogicException('Collector must be fully iterated first.');
            }

            return $this->hasMore;
        }
    }

    /**
     * Denotes if collection has been fully iterated.
     */
    private bool $exhausted = false;

    /**
     * @param iterable<TKey, TValue> $source Stream source to collect.
     */
    public function __construct(
        private readonly iterable $source,
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
        $iteration        = 0;
        $this->closed     = true;
        $this->aggregated = [];

        foreach ($this->source as $key => $value) {
            $iteration++;

            if (null !== $this->limit && $iteration === $this->limit) {
                yield $key => $value;
                $this->aggregated = $this->source instanceof StreamInterface ? $this->source->aggregated : [];
                continue;
            }

            if (null !== $this->limit && $iteration > $this->limit) {
                $this->hasMore   = true;
                $this->exhausted = true;
                return;
            }

            $this->aggregated = $this->source instanceof StreamInterface ? $this->source->aggregated : [];
            yield $key => $value;
        }

        $this->hasMore   = false;
        $this->exhausted = true;
    }
}
