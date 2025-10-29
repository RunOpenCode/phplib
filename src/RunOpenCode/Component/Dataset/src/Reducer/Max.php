<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reducer which calculates maximum value from a collection of values.
 *
 * Null values are ignored.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @phpstan-type ValueExtractor = callable(TValue, TKey): TReducedValue|null
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements ReducerInterface<TKey, TValue, TReducedValue|null>
 */
final class Max extends AbstractStream implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => $this->closed ? $this->value : throw new LogicException('Stream is not closed (iterated).');
    }

    private readonly \Closure $extractor;

    /**
     * @param iterable<TKey, TValue> $collection Collection of values to reduce.
     * @param ValueExtractor|null    $extractor  Optional value extractor. If not provided, values are used as is.
     */
    public function __construct(
        private readonly iterable $collection,
        ?callable                 $extractor = null
    ) {
        parent::__construct($this->collection);
        $this->extractor = (
            $extractor ??
            static fn(mixed $value): mixed => $value
        )(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->value = null;
        $current     = null;

        foreach ($this->collection as $key => $value) {
            /** @var TReducedValue|null $extracted */
            $extracted = ($this->extractor)($value, $key);

            if (null === $extracted) {
                yield $key => $value;
                continue;
            }

            if (null === $current || $extracted > $current) {
                $current     = $extracted;
                $this->value = $current;
            }

            yield $key => $value;
        }
    }
}
