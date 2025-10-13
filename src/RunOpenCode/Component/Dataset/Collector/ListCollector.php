<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Exception\OutOfBoundsException;

use function RunOpenCode\Component\Dataset\iterable_to_list;

/**
 * Collect iterable into list.
 *
 * @template TKey
 * @template TValue
 *
 * @implements CollectorInterface<list<TValue>>
 * @implements \IteratorAggregate<int, TValue>
 * @implements \ArrayAccess<int, TValue>
 */
final class ListCollector implements \IteratorAggregate, \Countable, \ArrayAccess, CollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value;

    /**
     * {@inheritdoc}
     */
    public private(set) array $aggregators;

    /**
     * {@inheritdoc}
     */
    public bool $closed {
        get => false;
    }

    /**
     * @param iterable<TKey, TValue> $collection Collection to collect.
     */
    public function __construct(
        private readonly iterable $collection,
    ) {
        $this->value       = iterable_to_list($this->collection);
        $this->aggregators = $this->collection instanceof StreamInterface ? \array_map(
            static fn(AggregatorInterface $aggregator): mixed => $aggregator->value,
            $this->collection->aggregators,
        ) : [];
    }

    /**
     * {@inheritdoc}
     *
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @param int $offset
     *
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->value[$offset] : throw new OutOfBoundsException($offset, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @param int    $offset
     * @param TValue $value
     *
     * @return never
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException(\sprintf(
            'Cannot set value for key "%s". Collector "%s" is read-only.',
            \var_export($offset, true),
            self::class,
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @param int $offset
     *
     * @return never
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException(\sprintf(
            'Cannot unset value for key "%s". Collector "%s" is read-only.',
            \var_export($offset, true),
            self::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        yield from $this->value;
    }
}
