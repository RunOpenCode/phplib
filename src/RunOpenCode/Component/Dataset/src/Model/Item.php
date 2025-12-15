<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Model;

use RunOpenCode\Component\Dataset\Exception\OutOfBoundsException;

/**
 * @template TKey
 * @template TValue
 *
 * @implements \ArrayAccess<int, TKey|TValue>
 */
final readonly class Item implements \ArrayAccess
{
    /**
     * @param TKey   $key
     * @param TValue $value
     */
    public function __construct(
        private mixed $key,
        private mixed $value
    ) {
        // noop.
    }

    /**
     * Get key.
     *
     * @return TKey
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * Get value.
     *
     * @return TValue
     */
    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return $offset === 0 || $offset === 1;
    }

    /**
     * {@inheritdoc}
     *
     * @return ($offset is 0 ? TKey : TValue)
     */
    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            0 => $this->key,
            1 => $this->value,
            default => throw new OutOfBoundsException($offset, $this, \sprintf(
                'Item tuple does not have offset "%s".',
                $offset
            )),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('Item is immutable.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('Item is immutable.');
    }
}
