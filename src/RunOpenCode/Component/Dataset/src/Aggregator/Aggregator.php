<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Aggregator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Aggregator.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @implements AggregatorInterface<TKey, TValue, TReducedValue>
 * @extends AbstractStream<TKey, TValue>
 *
 * @internal
 */
final class Aggregator extends AbstractStream implements AggregatorInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get {
            return $this->reducer->value;
        }
    }

    /**
     * Create new instance of aggregator.
     *
     * @param non-empty-string                              $name    Name of the aggregator.
     * @param ReducerInterface<TKey, TValue, TReducedValue> $reducer Reducer instance.
     */
    public function __construct(
        public readonly string            $name,
        private readonly ReducerInterface $reducer
    ) {
        parent::__construct($this->reducer);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        yield from $this->reducer;
    }
}
