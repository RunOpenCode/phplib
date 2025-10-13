<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset;

use RunOpenCode\Component\Dataset\Aggregator\Aggregator;
use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Reducer\Callback;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @param iterable<TKey, TValue> $iterable
 * @param bool                   $preserveKeys
 *
 * @return ($preserveKeys is true ? array<TKey, TValue> : list<TValue>)
 */
function iterable_to_array(iterable $iterable, bool $preserveKeys = true): array
{
    if (\is_array($iterable)) {
        return $preserveKeys ? $iterable : \array_values($iterable);
    }

    return \iterator_to_array($iterable, $preserveKeys);
}

/**
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $iterable
 *
 * @return list<TValue>
 */
function iterable_to_list(iterable $iterable): array
{
    if (\is_array($iterable)) {
        return \array_values($iterable);
    }

    return \iterator_to_array($iterable, false);
}

/**
 * Create batch operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 *
 * @return Stream<TKey, TValue>
 */
function stream(iterable $collection): Stream
{
    return new Stream($collection);
}

/**
 * Create batch operator.
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 * @template TModifiedValue
 *
 * @param iterable<TKey, TValue>                                                                                   $collection Collection to iterate over.
 * @param callable(iterable<array{TKey, TValue}> $batch, int $batchNumber): iterable<TModifiedKey, TModifiedValue> $onBatch    User defined callable to be called on each batch.
 * @param positive-int                                                                                             $size       Size of the batch buffer.
 *
 * @return Stream<TModifiedKey, TModifiedValue>
 *
 * @see Operator\Batch
 */
function batch(iterable $collection, callable $onBatch, int $size = 1000): Stream
{
    return new Stream(
        new Operator\Batch($collection, $onBatch, $size)
    );
}

/**
 * Create distinct operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>              $collection Collection to iterate over.
 * @param callable(TValue, TKey): string|null $identity   User defined callable to determine item identity. If null, strict comparison (===) is used.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Distinct
 */
function distinct(iterable $collection, ?callable $identity = null): Stream
{
    return new Stream(
        new Operator\Distinct($collection, $identity)
    );
}

/**
 * Create filter operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>       $collection Collection to iterate over.
 * @param callable(TValue, TKey): bool $filter     User defined callable to filter items.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Filter
 */
function filter(iterable $collection, callable $filter): Stream
{
    return new Stream(
        new Operator\Filter($collection, $filter)
    );
}

/**
 * Create flatten operator.
 *
 * @template TValue
 * @template TValues of iterable<TValue>
 *
 * @param iterable<mixed, TValues> $collection Collection to iterate over.
 *
 * @return Stream<int, TValue>
 *
 * @see Operator\Flatten
 */
function flatten(iterable $collection): Stream
{
    return new Stream(
        new Operator\Flatten($collection)
    );
}

/**
 * Create map operator.
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 * @template TModifiedValue
 *
 * @param iterable<TKey, TValue>                    $collection     Collection to iterate over.
 * @param callable(TValue, TKey): TModifiedValue    $valueTransform User defined callable to be called on each item.
 * @param callable(TKey, TValue): TModifiedKey|null $keyTransform   User defined callable to be called on each item key. If null, original keys are preserved.
 *
 * @return Stream<($keyTransform is null ? TModifiedKey : TKey), TModifiedValue>
 *
 * @see Operator\Map
 */
function map(iterable $collection, callable $valueTransform, ?callable $keyTransform = null): Stream
{
    /**
     * @var StreamInterface<($keyTransform is null ? TKey : TModifiedKey), TModifiedValue> $map
     */
    $map = new Operator\Map($collection, $valueTransform, $keyTransform);

    return new Stream($map);
}

/**
 * Create merge operator.
 *
 * @template TKey1
 * @template TValue1
 * @template TKey2
 * @template TValue2
 *
 * @param iterable<TKey1, TValue1> $first  First collection to iterate over.
 * @param iterable<TKey2, TValue2> $second Second collection to iterate over.
 *
 * @return Stream<TKey1|TKey2, TValue1|TValue2>
 *
 * @see Operator\Merge
 */
function merge(iterable $first, iterable $second): Stream
{
    return new Stream(
        new Operator\Merge($first, $second)
    );
}

/**
 * Create reverse operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over in reverse order.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Reverse
 */
function reverse(iterable $collection): Stream
{
    return new Stream(
        new Operator\Reverse($collection)
    );
}

/**
 * Create skip operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 * @param positive-int           $count      Number of items to skip.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Skip
 */
function skip(iterable $collection, int $count): Stream
{
    return new Stream(
        new Operator\Skip($collection, $count)
    );
}

/**
 * Create sort operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>               $collection Collection to iterate over.
 * @param (callable(TValue, TValue): int)|null $comparator User defined callable to compare two items. If null, spaceship operator (<=>) is used.
 * @param bool                                 $byKeys     If `byKeys` is true, keys will be compared instead of values.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Sort
 */
function sort(iterable $collection, ?callable $comparator = null, bool $byKeys = false): Stream
{
    return new Stream(
        new Operator\Sort($collection, $comparator, $byKeys)
    );
}

/**
 * Create take operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 * @param positive-int           $count      Number of items to yield.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Take
 */
function take(iterable $collection, int $count): Stream
{
    return new Stream(
        new Operator\Take($collection, $count)
    );
}

/**
 * Create take until operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>       $collection Collection to iterate over.
 * @param callable(TValue, TKey): bool $predicate  User defined callable to evaluate.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\TakeUntil
 */
function takeUntil(iterable $collection, callable $predicate): Stream
{
    return new Stream(
        new Operator\TakeUntil($collection, $predicate)
    );
}

/**
 * Create tap operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>       $collection Collection to iterate over.
 * @param callable(TValue, TKey): void $callback   User defined callable to execute for each item.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Tap
 */
function tap(iterable $collection, callable $callback): Stream
{
    return new Stream(
        new Operator\Tap($collection, $callback)
    );
}

/**
 * Attach reducer as an aggregator.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
 *
 * @param non-empty-string       $name       Name of the aggregator.
 * @param iterable<TKey, TValue> $collection Collection to collect from.
 * @param class-string<TReducer> $reducer    Reducer to attach
 * @param mixed                  ...$args    Arguments passed to reducer.
 *
 * @return Stream<TKey, TValue>
 */
function aggregate(string $name, iterable $collection, string $reducer, mixed ...$args): Stream
{
    /** @var TReducer $reducer */
    $reducer = new \ReflectionClass($reducer)->newInstanceArgs(\array_merge(
        [$collection],
        $args
    ));

    return new Stream(
        new Aggregator($name, $reducer),
    );
}

/**
 * Collect values from dataset using specified collector.
 *
 * @template TKey
 * @template TValue
 * @template TCollectedValue
 * @template TCollector of CollectorInterface<TCollectedValue>
 *
 * @param iterable<TKey, TValue>   $collection Collection to collect from.
 * @param class-string<TCollector> $collector  Collector class name.
 * @param mixed                    ...$args    Arguments passed to collector.
 *
 * @return TCollector
 *
 * @see Contract\CollectorInterface
 */
function collect(iterable $collection, string $collector, mixed ...$args): CollectorInterface
{
    return new \ReflectionClass($collector)->newInstanceArgs(\array_merge(
        [$collection],
        $args
    ));
}

/**
 * Reduce values from dataset using specified reducer.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
 *
 * @param iterable<TKey, TValue>                                                           $collection Collection to collect from.
 * @param class-string<TReducer>|callable(TReducedValue|null, TValue, TKey): TReducedValue $reducer    Reducer class name or callable.
 * @param mixed                                                                            ...$args    Arguments passed to reducer.
 *
 * @return TReducedValue
 *
 * @see Contract\ReducerInterface
 */
function reduce(iterable $collection, callable|string $reducer, mixed ...$args): mixed
{
    $isClassString = \is_string($reducer) && \is_a($reducer, ReducerInterface::class, true);
    $reducer       = $isClassString ? new \ReflectionClass($reducer)->newInstanceArgs(\array_merge(
        [$collection],
        $args
    )) : new Callback($collection, $reducer, ...$args);

    foreach ($reducer as $_) {
        // noop.
    }

    return $reducer->value;
}
