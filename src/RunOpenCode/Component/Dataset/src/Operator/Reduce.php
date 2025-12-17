<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reduce operator.
 *
 * This operator wraps reducers providing data streaming as well as
 * value reduction during the stream.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 *
 * @internal
 */
final class Reduce extends AbstractStream implements OperatorInterface
{
    /**
     * Current reduced value.
     *
     * @var TReducedValue
     */
    public mixed $value {
        get => $this->closed ? $this->reducer->value : throw new LogicException('Stream is not iterated.');
    }

    /**
     * Create reducing operator.
     *
     * @param iterable<TKey, TValue>                        $collection Collection of values to reduce.
     * @param ReducerInterface<TKey, TValue, TReducedValue> $reducer    Reducer to use.
     */
    public function __construct(
        private readonly iterable         $collection,
        private readonly ReducerInterface $reducer,
    ) {
        parent::__construct($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $key => $value) {
            $this->reducer->next($value, $key);

            yield $key => $value;
        }
    }
}
