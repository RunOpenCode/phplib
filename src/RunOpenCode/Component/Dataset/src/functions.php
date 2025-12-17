<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset;

use RunOpenCode\Component\Dataset\Aggregator\Aggregator;
use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * Transform iterable to array.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @param iterable<TKey, TValue> $iterable
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
 * Transform iterable to list.
 *
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
 * Create buffer count operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 * @param positive-int           $count      How many items to buffer.
 *
 * @return Stream<int, Buffer<TKey, TValue>>
 *
 * @see Operator\BufferCount
 */
function buffer_count(iterable $collection, int $count): Stream
{
    return new Stream(
        new Operator\BufferCount($collection, $count)
    );
}

/**
 * Create buffer while operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                               $collection Collection to iterate over.
 * @param callable(Buffer<TKey, TValue>, TValue=, TKey=): bool $predicate  Callable predicate function to evaluate.
 *
 * @return Stream<int, Buffer<TKey, TValue>>
 *
 * @see Operator\BufferWhile
 */
function buffer_while(iterable $collection, callable $predicate): Stream
{
    return new Stream(
        new Operator\BufferWhile($collection, $predicate)
    );
}

/**
 * Create distinct operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                 $collection Collection to iterate over.
 * @param (callable(TValue, TKey=): string)|null $identity   User defined callable to determine item identity. If null, strict comparison (===) is used.
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
 * @param iterable<TKey, TValue>        $collection Collection to iterate over.
 * @param callable(TValue, TKey=): bool $filter     User defined callable to filter items.
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
 * Create finalize operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 * @param callable(): void       $finalizer  User defined callable to invoke when iterator is depleted or exception is thrown.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Finalize
 */
function finalize(iterable $collection, callable $finalizer): Stream
{
    return new Stream(
        new Operator\Finalize($collection, $finalizer)
    );
}

/**
 * Create flatten operator.
 *
 * @template TKey
 * @template TValue
 * @template TValues of iterable<TKey, TValue>
 *
 * @param iterable<mixed, TValues> $collection   Collection to iterate over.
 * @param bool                     $preserveKeys Should keys be preserved from the flattened collections, false by default.
 *
 * @return ($preserveKeys is true ? Stream<TKey, TValue> : Stream<int, TValue>)
 *
 * @see                      Operator\Flatten
 *
 * @phpstan-ignore-next-line return.unusedType
 */
function flatten(iterable $collection, bool $preserveKeys = false): Stream
{
    // @phpstan-ignore-next-line return.type
    return new Stream(
        new Operator\Flatten($collection, $preserveKeys)
    );
}

/**
 * Iterate through stream without yielding items.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate.
 *
 * @return Stream<TKey, TValue> Flushed stream.
 */
function flush(iterable $collection): Stream
{
    $stream = new Stream($collection);

    \iterator_to_array($stream, false);

    return $stream;
}

/**
 * Create if empty operator.
 *
 * @template TKey
 * @template TValue
 * @template TAlternativeKey
 * @template TAlternativeValue
 *
 * @param iterable<TKey, TValue>                                                      $collection Collection to iterate over.
 * @param \Exception|(callable(): iterable<TAlternativeKey, TAlternativeValue>|never) $action     Action to undertake if collection is empty, or exception to throw.
 *
 * @return Stream<TKey|TAlternativeKey, TValue|TAlternativeValue>
 *
 * @see Operator\IfEmpty
 */
function if_empty(iterable $collection, \Exception|callable $action): Stream
{
    return new Stream(
        new Operator\IfEmpty($collection, $action)
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
 * @param iterable<TKey, TValue>                     $collection     Collection to iterate over.
 * @param callable(TValue, TKey=): TModifiedValue    $valueTransform User defined callable to be called on each item.
 * @param callable(TKey, TValue=): TModifiedKey|null $keyTransform   User defined callable to be called on each item key. If null, original keys are preserved.
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
 * Create overflow operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $collection Collection to iterate over.
 * @param positive-int           $capacity   Max number of items to iterate over.
 * @param \Exception|null        $exception  Which exception to throw if collection has more then allowed items ({@see \OverflowException} by default).
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Overflow
 */
function overflow(iterable $collection, int $capacity, ?\Exception $exception = null): Stream
{
    return new Stream(
        new Operator\Overflow($collection, $capacity, $exception)
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
 * @param iterable<TKey, TValue>                                                                  $collection Collection to iterate over.
 * @param ($byKeys is false ? (callable(TValue, TValue): int) : (callable(TKey, TKey): int))|null $comparator User defined callable to compare two items. If null, spaceship operator (<=>) is used.
 * @param bool                                                                                    $byKeys     If `byKeys` is true, keys will be compared instead of values.
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
 * @param non-empty-string                                                             $name       Name of the aggregator.
 * @param iterable<TKey, TValue>                                                       $collection Collection to collect from.
 * @param class-string<TReducer>|callable(TReducedValue, TValue, TKey=): TReducedValue $reducer    Reducer to attach.
 * @param mixed                                                                        ...$args    Arguments passed to reducer.
 *
 * @return Stream<TKey, TValue>
 */
function aggregate(string $name, iterable $collection, callable|string $reducer, mixed ...$args): Stream
{
    /** @var TReducer $instance */
    $instance = \is_string($reducer) && \is_a($reducer, ReducerInterface::class, true)
        ? new \ReflectionClass($reducer)->newInstanceArgs($args)
        : new Reducer\Callback($reducer, ...$args);

    return new Stream(
        new Aggregator($name, new Operator\Reduce($collection, $instance)),
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
 * @param iterable<TKey, TValue>                                                       $collection Collection to collect from.
 * @param class-string<TReducer>|callable(TReducedValue, TValue, TKey=): TReducedValue $reducer    Reducer to use.
 * @param mixed                                                                        ...$args    Arguments passed to reducer.
 *
 * @return TReducedValue
 *
 * @see Contract\ReducerInterface
 */
function reduce(iterable $collection, callable|string $reducer, mixed ...$args): mixed
{
    /** @var TReducer $instance */
    $instance = \is_string($reducer) && \is_a($reducer, ReducerInterface::class, true)
        ? new \ReflectionClass($reducer)->newInstanceArgs($args)
        : new Reducer\Callback($reducer, ...$args);

    $operator = new Operator\Reduce($collection, $instance);

    flush($operator);

    return $operator->value;
}
