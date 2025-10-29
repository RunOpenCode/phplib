<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reducer which uses custom callback function to reduce items to value.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue = mixed
 *
 * @phpstan-type ReducerCallable = callable(TReducedValue, TValue, TKey): TReducedValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements ReducerInterface<TKey, TValue, TReducedValue>
 */
final class Callback extends AbstractStream implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => $this->closed ? $this->value : throw new LogicException('Stream is not closed (iterated).');
    }

    private readonly \Closure $callback;

    /**
     * @param iterable<TKey, TValue> $collection Collection of values to reduce.
     * @param ReducerCallable        $callback   Callback function used to reduce values.
     * @param mixed                  $initial    Initial value.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $callback,
        private readonly mixed    $initial = null
    ) {
        parent::__construct($collection);
        $this->callback = $callback(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->value = $this->initial;
        $carry       = $this->initial;

        foreach ($this->collection as $key => $value) {
            $carry       = ($this->callback)($carry, $value, $key);
            $this->value = $carry;

            yield $key => $value;
        }
    }
}
