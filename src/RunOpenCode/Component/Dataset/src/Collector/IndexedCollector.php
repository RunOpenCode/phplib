<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Collector;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Exception\OutOfBoundsException;
use RunOpenCode\Component\Dataset\Exception\UnsupportedException;

/**
 * Collects items into an iterable, indexing values by their keys.
 *
 * The collector assumes that keys are not unique; therefore, accessing
 * a value by key returns a list of values.
 *
 * Currently, the allowed key types are scalar values
 * (int, float, string, bool, null) and objects.
 *
 * @template TKey
 * @template TValue
 *
 * @implements CollectorInterface<iterable<TKey,TValue>>
 * @implements \IteratorAggregate<TKey, TValue>
 * @implements \ArrayAccess<TKey, list<TValue>>
 */
final class IndexedCollector implements CollectorInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => $this->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public array $aggregated {
        get => $this->source instanceof StreamInterface ? $this->source->aggregated : [];
    }

    /**
     * {@inheritdoc}
     */
    public bool $closed {
        get => false;
    }

    /**
     * Index of values with keys of scalar type.
     *
     * @var array<TKey, list<TValue>>
     */
    private array $scalarIndex = [];

    /**
     * Index of values with keys of object type.
     *
     * @var \SplObjectStorage<TKey&object, list<TValue>>
     */
    private \SplObjectStorage $objectIndex;

    /**
     * Collected values from stream.
     *
     * @var array<array{TKey, TValue}>
     */
    private array $collected = [];

    /**
     * @param iterable<TKey, TValue> $source Stream source to collect.
     */
    public function __construct(
        private readonly iterable $source,
    ) {
        $this->objectIndex = new \SplObjectStorage();

        foreach ($this->source as $key => $value) {
            $this->collected[] = [$key, $value];

            if (\is_string($key) || \is_int($key)) {
                $this->scalarIndex[$key]   = $this->scalarIndex[$key] ?? [];
                $this->scalarIndex[$key][] = $value;
                continue;
            }

            if (\is_object($key)) {
                $current = $this->objectIndex->contains($key) ? $this->objectIndex[$key] : [];

                $current[]               = $value;
                $this->objectIndex[$key] = $current;
                continue;
            }

            throw new UnsupportedException('Only object, string and integer keys are supported.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->collected as [$key, $value]) {
            yield $key => $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return match (true) {
            \is_string($offset) || \is_int($offset) => \array_key_exists($offset, $this->scalarIndex),
            \is_object($offset) => $this->objectIndex->contains($offset),
            default => throw new UnsupportedException('Only object, string and integer keys are supported.'),
        };
    }

    /**
     * Get values for given key.
     *
     * @param TKey $offset
     *
     * @return list<TValue>
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException($offset, $this->value);
        }

        return match (true) {
            \is_string($offset) || \is_int($offset) => $this->scalarIndex[$offset],
            \is_object($offset) => $this->objectIndex[$offset],
            default => throw new UnsupportedException('Only object, string and integer keys are supported.'),
        };
    }

    /**
     * {@inheritdoc}
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
        return \count($this->collected);
    }
}
