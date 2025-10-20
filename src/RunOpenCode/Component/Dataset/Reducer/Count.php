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

    private \Closure $callback;

    /**
     * @param iterable<TKey, TValue>            $collection Collection of values to reduce.
     * @param callable(TValue, TKey): bool|null $filter     Optional filter callback to count only items that match the filter.
     */
    public function __construct(
        private readonly iterable $collection,
        ?callable                 $filter = null,
    ) {
        parent::__construct($this->collection);
        $this->callback = $filter ? $filter(...) : static fn(): bool => true;
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->value = 0;

        foreach ($this->collection as $key => $value) {
            if (!($this->callback)($value, $key)) {
                yield $key => $value;
                continue;
            }

            $this->value++;
            yield $key => $value;
        }
    }
}
