<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;

/**
 * Collect as original stream, rewindable.
 *
 * Similar to IterableCollector, but allows you to iterate through whole
 * stream multiple times providing you the access to aggregators when stream is iterated.
 *
 * @template TKey
 * @template TValue
 *
 * @implements CollectorInterface<iterable<TKey, TValue>>
 * @implements \IteratorAggregate<TKey, TValue>
 */
final class RewindableCollector implements \IteratorAggregate, CollectorInterface
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
            if (!$this->depleted) {
                $this->flush();
            }

            return $this->source instanceof StreamInterface ? $this->source->aggregated : [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $closed {
        get => false;
    }

    /**
     * Provides you with total number of iterated elements.
     *
     * @var non-negative-int
     */
    public private(set) int $count = 0;

    private bool $depleted = false;

    /** @var list<array{TKey, TValue}> */
    private array $buffer = [];

    /**
     * @param iterable<TKey, TValue> $source Stream source to collect.
     */
    public function __construct(
        private readonly iterable $source,
    ) {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        $this->flush();

        $this->count = 0;

        foreach ($this->buffer as [$key, $value]) {
            yield $key => $value;
            $this->count++;
        }
    }

    private function flush(): void
    {
        if ($this->depleted) {
            return;
        }

        foreach ($this->source as $key => $value) {
            $this->buffer[] = [$key, $value];
        }

        $this->depleted = true;
    }
}
