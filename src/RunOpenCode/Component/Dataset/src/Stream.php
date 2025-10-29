<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset;

use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

use function RunOpenCode\Component\Dataset\aggregate as dataset_aggregate;
use function RunOpenCode\Component\Dataset\batch as dataset_batch;
use function RunOpenCode\Component\Dataset\collect as dataset_collect;
use function RunOpenCode\Component\Dataset\distinct as dataset_distinct;
use function RunOpenCode\Component\Dataset\filter as dataset_filter;
use function RunOpenCode\Component\Dataset\flatten as dataset_flatten;
use function RunOpenCode\Component\Dataset\map as dataset_map;
use function RunOpenCode\Component\Dataset\merge as dataset_merge;
use function RunOpenCode\Component\Dataset\reduce as dataset_reduce;
use function RunOpenCode\Component\Dataset\reverse as dataset_reverse;
use function RunOpenCode\Component\Dataset\skip as dataset_skip;
use function RunOpenCode\Component\Dataset\sort as dataset_sort;
use function RunOpenCode\Component\Dataset\take as dataset_take;
use function RunOpenCode\Component\Dataset\takeUntil as dataset_take_until;
use function RunOpenCode\Component\Dataset\tap as dataset_tap;

/**
 * Dataset iterable stream.
 *
 * You may extend this class to add your own custom operators.
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 */
class Stream extends AbstractStream
{
    /**
     * @param iterable<TKey, TValue> $collection
     */
    public function __construct(
        private readonly iterable $collection,
    ) {
        parent::__construct($collection);
    }

    /**
     * Creates new instance of iterable stream.
     *
     * @template Key
     * @template Value
     *
     * @param iterable<Key, Value> $collection Collection to wrap.
     *
     * @return self<Key, Value>
     */
    public static function create(iterable $collection): self
    {
        return new self($collection);
    }

    /**
     * Applies batch operator on current stream.
     *
     * @template TModifiedKey
     * @template TModifiedValue
     *
     * @param callable(iterable<array{TKey, TValue}> $batch, int $batchNumber): iterable<TModifiedKey, TModifiedValue> $onBatch User defined callable to be called on each batch.
     * @param positive-int                                                                                             $size    Size of the batch buffer.
     *
     * @return self<TModifiedKey, TModifiedValue>
     *
     * @see Operator\Batch
     */
    public function batch(callable $onBatch, int $size = 1000): self
    {
        return dataset_batch($this, $onBatch, $size);
    }

    /**
     * Applies distinct operator on current stream.
     *
     * @param callable(TValue, TKey): string|null $identity User defined callable to determine item identity. If null, strict comparison (===) is used.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Distinct
     */
    public function distinct(?callable $identity = null): self
    {
        return dataset_distinct($this, $identity);
    }

    /**
     * Applies filter operator on current stream.
     *
     * @param callable(TValue, TKey): bool $filter User defined callable to filter items.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Filter
     */
    public function filter(callable $filter): self
    {
        return dataset_filter($this, $filter);
    }

    /**
     * Applies flatten operator on current stream.
     *
     * @return self<int, mixed>
     *
     * @see Operator\Flatten
     */
    public function flatten(): self
    {
        return dataset_flatten($this);
    }

    /**
     * Applies map operator on current stream.
     *
     * @template TModifiedKey
     * @template TModifiedValue
     *
     * @param callable(TValue, TKey): TModifiedValue    $valueTransform User defined callable to be called on each item.
     * @param callable(TKey, TValue): TModifiedKey|null $keyTransform   User defined callable to be called on each item key. If null, original keys are preserved.
     *
     * @return self<($keyTransform is null ? TKey : TModifiedKey), TModifiedValue>
     *
     * @see Operator\Map
     */
    public function map(callable $valueTransform, ?callable $keyTransform = null): self
    {
        return dataset_map($this, $valueTransform, $keyTransform);
    }

    /**
     * Applies merge operator on current stream.
     *
     * @template TKeyOther
     * @template TValueOther
     *
     * @param iterable<TKeyOther, TValueOther> $collection Collection to merge with current stream.
     *
     * @return self<TKey|TKeyOther, TValue|TValueOther>
     *
     * @see Operator\Merge
     */
    public function merge(iterable $collection): self
    {
        return dataset_merge($this, $collection);
    }

    /**
     * Applies reverse operator on current stream.
     *
     * WARNING: This operator will load entire collection into memory!
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Reverse
     */
    public function reverse(): self
    {
        return dataset_reverse($this);
    }

    /**
     * Applies skip operator on current stream.
     *
     * @param positive-int $count Number of items to skip.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Skip
     */
    public function skip(int $count): self
    {
        return dataset_skip($this, $count);
    }

    /**
     * Applies sort operator on current stream.
     *
     * WARNING: This operator will load entire collection into memory!
     *
     * @param (callable(TValue, TValue): int)|null $comparator User defined callable to compare two items. If null, spaceship operator (<=>) is used.
     * @param bool                                 $byKeys     If `byKeys` is true, keys will be compared instead of values.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Sort
     */
    public function sort(?callable $comparator = null, bool $byKeys = false): self
    {
        return dataset_sort($this, $comparator, $byKeys);
    }

    /**
     * Applies take operator on current stream.
     *
     * @param positive-int $count Number of items to yield.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Take
     */
    public function take(int $count): self
    {
        return dataset_take($this, $count);
    }

    /**
     * Applies takeUntil operator on current stream.
     *
     * @param callable(TValue, TKey): bool $predicate User defined callable to evaluate.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\TakeUntil
     */
    public function takeUntil(callable $predicate): self
    {
        return dataset_take_until($this, $predicate);
    }

    /**
     * Applies tap operator on current stream.
     *
     * @param callable(TValue, TKey): void $callback User defined callable to be called on each item.
     *
     * @return self<TKey, TValue>
     *
     * @see Operator\Tap
     */
    public function tap(callable $callback): self
    {
        return dataset_tap($this, $callback);
    }

    /**
     * @template TReducedValue
     * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
     *
     * @param non-empty-string       $name    Name of the aggregation.
     * @param class-string<TReducer> $reducer Reducer to user for aggregation.
     * @param mixed                  ...$args Arguments passed to reducer.
     *
     * @return self<TKey, TValue>
     */
    public function aggregate(string $name, string $reducer, mixed ...$args): self
    {
        return dataset_aggregate($name, $this, $reducer, ...$args);
    }

    /**
     * Collect values from dataset using specified collector.
     *
     * @template TCollectedValue
     * @template TCollector of CollectorInterface<TCollectedValue>
     *
     * @param class-string<TCollector> $collector Collector class name.
     * @param mixed                    ...$args   Arguments passed to collector.
     *
     * @return TCollector
     *
     * @see Contract\CollectorInterface
     */
    public function collect(string $collector, mixed ...$args): CollectorInterface
    {
        return dataset_collect($this, $collector, ...$args);
    }

    /**
     * Reduce values from dataset using specified reducer.
     *
     * @template TReducedValue
     * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
     *
     * @param class-string<TReducer>|callable(TReducedValue|null, TValue, TKey): TReducedValue $reducer Reducer class name or callable.
     * @param mixed                                                                            ...$args Arguments passed to reducer.
     *
     * @return TReducedValue
     *
     * @see Contract\ReducerInterface
     */
    public function reduce(callable|string $reducer, mixed ...$args): mixed
    {
        return dataset_reduce($this, $reducer, ...$args);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        yield from $this->collection;
    }
}
