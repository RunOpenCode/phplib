<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Model;

use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Stream;

/**
 * Buffer of iterated items from collection.
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @phpstan-type ItemTuple = array{TKey, TValue}
 *
 * @implements \IteratorAggregate<TKey, TValue>
 */
final readonly class Buffer implements \IteratorAggregate, \Countable
{
    /**
     * Create new buffer.
     *
     * @param \ArrayObject<int, ItemTuple> $items Items within buffer.
     *
     * @internal
     */
    public function __construct(private \ArrayObject $items)
    {
        // noop.
    }

    /**
     * Create stream from buffer.
     *
     * @return Stream<TKey, TValue>
     *
     * @phpstan-ignore-next-line generics.variance
     */
    public function stream(): Stream
    {
        return new Stream($this);
    }

    /**
     * Get first item in buffer.
     *
     * @return Item<TKey, TValue>
     *
     * @phpstan-ignore-next-line generics.variance
     */
    public function first(): Item
    {
        $first = $this->items[0] ?? throw new LogicException('Buffer is empty.');

        return new Item($first[0], $first[1]);
    }

    /**
     * Get last item in buffer.
     *
     * @return Item<TKey, TValue>
     *
     * @phpstan-ignore-next-line generics.variance
     */
    public function last(): Item
    {
        $last = $this->items[\count($this->items) - 1] ?? throw new LogicException('Buffer is empty.');

        return new Item($last[0], $last[1]);
    }

    /**
     * Get all keys.
     *
     * @return list<TKey>
     */
    public function keys(): array
    {
        $keys = [];

        foreach ($this->items as [$key]) {
            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * Get all values.
     *
     * @return list<TValue>
     */
    public function values(): array
    {
        $values = [];

        foreach ($this->items as [, $value]) {
            $values[] = $value;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->items as [$key, $value]) {
            yield $key => $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->items->count();
    }
}
