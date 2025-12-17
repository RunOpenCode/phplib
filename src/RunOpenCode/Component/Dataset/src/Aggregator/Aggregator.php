<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Aggregator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Operator\Reduce;

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
     * @param non-empty-string                    $name    Name of the aggregator.
     * @param Reduce<TKey, TValue, TReducedValue> $reducer Instance of reduce operator.
     */
    public function __construct(
        public readonly string  $name,
        private readonly Reduce $reducer
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
