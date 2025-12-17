<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Exception\OutOfBoundsException;

use function RunOpenCode\Component\Dataset\iterable_to_array;

/**
 * Collect iterable into array.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectorInterface<array<TKey, TValue>>
 * @implements \IteratorAggregate<TKey, TValue>
 * @implements \ArrayAccess<TKey, TValue>
 */
final class ArrayCollector implements \IteratorAggregate, \Countable, \ArrayAccess, CollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value;

    /**
     * {@inheritdoc}
     */
    public array $aggregated {
        get => $this->collection instanceof StreamInterface ? $this->collection->aggregated : [];
    }

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
        public readonly iterable $collection,
    ) {
        $this->value = iterable_to_array($this->collection);
    }

    /**
     * {@inheritdoc}
     *
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @param TKey $offset
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
     * @param TKey   $offset
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
     * @param TKey $offset
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
