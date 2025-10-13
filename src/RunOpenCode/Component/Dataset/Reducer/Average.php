<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

/**
 * Reducer which calculates average of values from a collection of values.
 *
 * Null values are ignored. You may define if null values are included in
 * the count of items when calculating average.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type ValueExtractor = callable(TValue, TKey): (int|float|null)
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements ReducerInterface<TKey, TValue, int|float|null>
 */
final class Average extends AbstractStream implements ReducerInterface
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
     * @param bool                   $countNull  Whether null values should be counted when calculating average.
     */
    public function __construct(
        private readonly iterable $collection,
        ?callable                 $extractor = null,
        private readonly bool     $countNull = false,
    ) {
        parent::__construct($collection);
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
        $total = 0;
        $count = 0;

        foreach ($this->collection as $key => $value) {
            /** @var int|float|null $extracted */
            $extracted = ($this->extractor)($value, $key);

            if (null === $extracted) {
                $count       = $this->countNull ? $count + 1 : $count;
                $this->value = (0 === $count) ? 0 : ($total / $count);
                yield $key => $value;
                continue;
            }

            $total += $extracted;
            $count++;
            $this->value = $total / $count;

            yield $key => $value;
        }
    }
}
