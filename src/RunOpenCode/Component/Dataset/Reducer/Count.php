<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reducer which counts number of items.
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements ReducerInterface<TKey, TValue, int>
 */
final class Count extends AbstractStream implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => $this->closed ? $this->value : throw new LogicException('Stream is not closed (iterated).');
    }
    /**
     * @param iterable<TKey, TValue> $collection Collection of values to reduce.
     */
    public function __construct(
        private readonly iterable $collection,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->value = 0;

        foreach ($this->collection as $key => $value) {
            $this->value++; // @phpstan-ignore-line
            yield $key => $value;
        }
    }
}
