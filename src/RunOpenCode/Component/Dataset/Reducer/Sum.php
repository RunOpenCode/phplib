<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reducer which calculates sum of values from a collection of values.
 *
 * Null values are ignored.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type ValueExtractor = callable(TValue, TKey): (int|float|null)
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements ReducerInterface<TKey, TValue, int|float|null>
 */
final class Sum extends AbstractStream implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public mixed $value {
        get => $this->closed ? $this->value : throw new LogicException('Stream is not closed (iterated).');
    }

    private \Closure $extractor;

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
            static fn(mixed $value): int|float|null => \is_numeric($value) ? $value + 0 : null
        )(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $this->value = null;

        foreach ($this->collection as $key => $value) {
            /** @var int|float|null $extracted */
            $extracted = ($this->extractor)($value, $key);

            if (null === $extracted) {
                yield $key => $value;
                continue;
            }

            // @phpstan-ignore-next-line
            $this->value = $extracted + ($this->value ?? 0);

            yield $key => $value;
        }
    }
}
