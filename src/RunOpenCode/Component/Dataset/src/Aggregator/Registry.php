<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Aggregator;

use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Registry of aggregators.
 *
 * @implements \IteratorAggregate<non-empty-string, AggregatorInterface<mixed, mixed, mixed>>
 * @implements \ArrayAccess<non-empty-string, AggregatorInterface<mixed, mixed, mixed>>
 *
 * @internal
 */
final class Registry implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array<non-empty-string, AggregatorInterface<mixed, mixed, mixed>> $aggregators
     */
    public array $aggregators = [];

    /**
     * Register an aggregator in the registry.
     *
     * @param AggregatorInterface<mixed, mixed, mixed> $aggregator
     */
    public function register(AggregatorInterface $aggregator): void
    {
        $this[$aggregator->name] = $aggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->aggregators[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @return AggregatorInterface<mixed, mixed, mixed>
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->aggregators[$offset] ?? throw new LogicException(\sprintf(
            'Aggregator with name "%s" is not registered in the registry.',
            $offset
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        \assert(\is_string($offset) && '' !== \trim($offset), 'Aggregator name must be a string.');

        if (isset($this->aggregators[$offset]) && $this->aggregators[$offset] !== $value) {
            throw new LogicException(\sprintf(
                'Aggregator with name "%s" is already registered in the registry.',
                $offset
            ));
        }

        if ($value->name !== $offset) {
            throw new LogicException(\sprintf(
                'Aggregator name mismatch. Given offset is "%s" while aggregator name is "%s".',
                $offset,
                $value->name
            ));
        }

        $this->aggregators[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->aggregators[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->aggregators);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        yield from $this->aggregators;
    }
}
