<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset;

use RunOpenCode\Component\Dataset\Aggregator\Aggregator;
use RunOpenCode\Component\Dataset\Contract\CollectorInterface;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Contract\StreamInterface;
use RunOpenCode\Component\Dataset\Exception\StreamOverflowException;
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
 * Create new stream.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate over.
 *
 * @return Stream<TKey, TValue>
 */
function stream(iterable $source): Stream
{
    return new Stream($source);
}

/**
 * Create buffer count operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate over.
 * @param positive-int           $count  Number of items to store into buffer.
 *
 * @return Stream<int, Buffer<TKey, TValue>>
 *
 * @see Operator\BufferCount
 */
function buffer_count(iterable $source, int $count): Stream
{
    return new Stream(
        new Operator\BufferCount($source, $count)
    );
}

/**
 * Create buffer while operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                               $source    Stream source to iterate over.
 * @param callable(Buffer<TKey, TValue>, TValue=, TKey=): bool $predicate Predicate function to evaluate if current item should be placed into existing buffer, or
 *                                                                        existing buffer should be yielded and new one should be created with current item.
 *
 * @return Stream<int, Buffer<TKey, TValue>>
 *
 * @see Operator\BufferWhile
 */
function buffer_while(iterable $source, callable $predicate): Stream
{
    return new Stream(
        new Operator\BufferWhile($source, $predicate)
    );
}

/**
 * Create distinct operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                 $source   Stream source to iterate over.
 * @param (callable(TValue, TKey=): string)|null $identity User defined callable to determine item identity. If null, strict comparison (===) is used.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Distinct
 */
function distinct(iterable $source, ?callable $identity = null): Stream
{
    return new Stream(
        new Operator\Distinct($source, $identity)
    );
}

/**
 * Create filter operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>        $source Stream source to iterate over.
 * @param callable(TValue, TKey=): bool $filter User defined callable to filter items.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Filter
 */
function filter(iterable $source, callable $filter): Stream
{
    return new Stream(
        new Operator\Filter($source, $filter)
    );
}

/**
 * Create finalize operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source    Stream source to iterate over.
 * @param callable(): void       $finalizer User defined callable to invoke when iterator is depleted, or exception is
 *                                          thrown, or operator instance is garbage collected.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Finalize
 */
function finalize(iterable $source, callable $finalizer): Stream
{
    return new Stream(
        new Operator\Finalize($source, $finalizer)
    );
}

/**
 * Create flatten operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<mixed, iterable<TKey, TValue>> $source       Stream of streams to iterate over.
 * @param bool                                    $preserveKeys Should keys be preserved from the flattened stream, false by default.
 *
 * @return ($preserveKeys is true ? Stream<TKey, TValue> : Stream<int, TValue>)
 *
 * @see Operator\Flatten
 *
 * @phpstan-ignore-next-line
 */
function flatten(iterable $source, bool $preserveKeys = false): Stream
{
    // @phpstan-ignore-next-line return.type
    return new Stream(
        new Operator\Flatten($source, $preserveKeys)
    );
}

/**
 * Iterate through stream without yielding items.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate.
 *
 * @return Stream<TKey, TValue> Flushed and closed stream.
 */
function flush(iterable $source): Stream
{
    $stream = new Stream($source);

    \iterator_to_array($stream, false);

    return $stream;
}

/**
 * Create "if empty" operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                               $source   Stream source to iterate over.
 * @param \Throwable|(callable(): iterable<TKey, TValue>)|null $fallback Fallback stream source, or exception to throw.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\IfEmpty
 */
function if_empty(iterable $source, \Throwable|callable|null $fallback): Stream
{
    return new Stream(
        new Operator\IfEmpty($source, $fallback)
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
 * @param iterable<TKey, TValue>                       $source         Stream source to iterate over.
 * @param callable(TValue, TKey=): TModifiedValue|null $valueTransform Optional transformation function for transforming values.
 * @param callable(TKey, TValue=): TModifiedKey|null   $keyTransform   Optional transformation function for transforming keys.
 *
 * @return Stream<($keyTransform is null ? TKey : TModifiedKey), ($valueTransform is null ? TValue : TModifiedValue)>
 *
 * @see Operator\Map
 */
function map(iterable $source, ?callable $valueTransform = null, ?callable $keyTransform = null): Stream
{
    /**
     * @var StreamInterface<($keyTransform is null ? TKey : TModifiedKey), ($valueTransform is null ? TValue : TModifiedValue)> $map
     */
    $map = new Operator\Map($source, $valueTransform, $keyTransform);

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
 * @param iterable<TKey1, TValue1> $first  First stream source to iterate over.
 * @param iterable<TKey2, TValue2> $second Second stream source to iterate over.
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
 * Create left join operator.
 *
 * @template TKey
 * @template TLeftValue
 * @template TRightValue
 *
 * @param iterable<TKey, TLeftValue>  $source Stream source to iterate over on the left side of the left join operation.
 * @param iterable<TKey, TRightValue> $join   Stream source to iterate over on the right side of the left join operation.
 *
 * @return Stream<TKey, array{TLeftValue, iterable<TRightValue>}>
 *
 * @see Operator\LeftJoin
 */
function left_join(iterable $source, iterable $join): Stream
{
    return new Stream(
        new Operator\LeftJoin($source, $join)
    );
}

/**
 * Create overflow operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                                           $source   Collection to iterate over.
 * @param positive-int                                                     $capacity Maximum number of items to iterate over.
 * @param \Throwable|(callable(StreamOverflowException=): \Throwable)|null $throw    Exception to throw if stream yielded more items then capacity allows.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Overflow
 */
function overflow(iterable $source, int $capacity, \Throwable|callable|null $throw = null): Stream
{
    return new Stream(
        new Operator\Overflow($source, $capacity, $throw)
    );
}

/**
 * Create reverse operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate over in reverse order.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Reverse
 */
function reverse(iterable $source): Stream
{
    return new Stream(
        new Operator\Reverse($source)
    );
}

/**
 * Create skip operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate over.
 * @param positive-int           $count  Number of items to skip.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Skip
 */
function skip(iterable $source, int $count): Stream
{
    return new Stream(
        new Operator\Skip($source, $count)
    );
}

/**
 * Create sort operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                                                                  $source     Stream source to iterate over.
 * @param ($byKeys is false ? (callable(TValue, TValue): int) : (callable(TKey, TKey): int))|null $comparator User defined callable to compare two items. If null, spaceship operator (<=>) is used.
 * @param bool                                                                                    $byKeys     If `byKeys` is true, keys will be compared instead of values.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Sort
 */
function sort(iterable $source, ?callable $comparator = null, bool $byKeys = false): Stream
{
    return new Stream(
        new Operator\Sort($source, $comparator, $byKeys)
    );
}

/**
 * Create take operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $source Stream source to iterate over.
 * @param positive-int           $count  Number of items to yield.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Take
 */
function take(iterable $source, int $count): Stream
{
    return new Stream(
        new Operator\Take($source, $count)
    );
}

/**
 * Create take until operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>        $source    Stream source to iterate over.
 * @param callable(TValue, TKey=): bool $predicate Predicate callable to evaluate stop condition.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\TakeUntil
 */
function takeUntil(iterable $source, callable $predicate): Stream
{
    return new Stream(
        new Operator\TakeUntil($source, $predicate)
    );
}

/**
 * Create tap operator.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>        $source   Stream source to iterate over.
 * @param callable(TValue, TKey=): void $callback Callable to execute for each item.
 *
 * @return Stream<TKey, TValue>
 *
 * @see Operator\Tap
 */
function tap(iterable $source, callable $callback): Stream
{
    return new Stream(
        new Operator\Tap($source, $callback)
    );
}

/**
 * Create custom operator.
 *
 * @template TInputKey
 * @template TInputValue
 * @template TOutputKey
 * @template TOutputValue
 *
 * @param iterable<TInputKey, TInputValue>                          $source       Stream source to iterate over.
 * @param class-string<OperatorInterface<TOutputKey, TOutputValue>> $operator     Operator to apply.
 * @param mixed                                                     ...$arguments Arguments for operator.
 *
 * @return Stream<TOutputKey, TOutputValue>
 */
function operator(iterable $source, string $operator, mixed ...$arguments): Stream
{
    return new Stream(
        new $operator($source, ...$arguments)
    );
}

/**
 * Attach reducer as an aggregator to stream source.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
 *
 * @param non-empty-string                                                             $name    Name of the aggregator.
 * @param iterable<TKey, TValue>                                                       $source  Stream source from which to aggregate values.
 * @param class-string<TReducer>|callable(TReducedValue, TValue, TKey=): TReducedValue $reducer Reducer to attach.
 * @param mixed                                                                        ...$args Arguments passed to reducer.
 *
 * @return Stream<TKey, TValue>
 */
function aggregate(string $name, iterable $source, callable|string $reducer, mixed ...$args): Stream
{
    /** @var TReducer $instance */
    $instance = \is_string($reducer) && \is_a($reducer, ReducerInterface::class, true)
        ? new \ReflectionClass($reducer)->newInstanceArgs($args)
        : new Reducer\Callback($reducer, ...$args);

    return new Stream(
        new Aggregator($name, new Operator\Reduce($source, $instance)),
    );
}

/**
 * Collect values from given stream source using specified collector.
 *
 * @template TKey
 * @template TValue
 * @template TCollectedValue
 * @template TCollector of CollectorInterface<TCollectedValue>
 *
 * @param iterable<TKey, TValue>   $source    Stream source to collect from.
 * @param class-string<TCollector> $collector Collector class name.
 * @param mixed                    ...$args   Arguments passed to collector.
 *
 * @return TCollector
 *
 * @see Contract\CollectorInterface
 */
function collect(iterable $source, string $collector, mixed ...$args): CollectorInterface
{
    return new \ReflectionClass($collector)->newInstanceArgs(\array_merge(
        [$source],
        $args
    ));
}

/**
 * Reduce values from stream source using specified reducer.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 * @template TReducer of ReducerInterface<TKey, TValue, TReducedValue>
 *
 * @param iterable<TKey, TValue>                                                       $source  Stream source to reduce.
 * @param class-string<TReducer>|callable(TReducedValue, TValue, TKey=): TReducedValue $reducer Reducer to use.
 * @param mixed                                                                        ...$args Arguments passed to reducer.
 *
 * @return TReducedValue
 *
 * @see Contract\ReducerInterface
 */
function reduce(iterable $source, callable|string $reducer, mixed ...$args): mixed
{
    /** @var TReducer $instance */
    $instance = \is_string($reducer) && \is_a($reducer, ReducerInterface::class, true)
        ? new \ReflectionClass($reducer)->newInstanceArgs($args)
        : new Reducer\Callback($reducer, ...$args);

    $operator = new Operator\Reduce($source, $instance);

    flush($operator);

    return $operator->value;
}

/**
 * Reduces values from stream source to their average.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                        $source    Stream source to reduce.
 * @param int|float|null                                $initial   Initial value to start with.
 * @param callable(TValue, TKey): (int|float|null)|null $extractor Optional function to extract reducible value.
 * @param bool                                          $countNull Should `null` values be accounted for, `false` by default.
 *
 * @return float|null Average of values.
 */
function average(iterable $source, int|float|null $initial = null, ?callable $extractor = null, bool $countNull = false): float|null
{
    return reduce($source, Reducer\Average::class, $initial, $extractor, $countNull);
}

/**
 * Reduces values from stream source to their count.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>              $source Stream source to reduce.
 * @param (callable(TValue, TKey): bool)|null $filter Optional filter callback to count only items that match the filter.
 *
 * @return int Number of items in the stream.
 */
function count(iterable $source, ?callable $filter = null): int
{
    return reduce($source, Reducer\Count::class, $filter);
}

/**
 * Reduces values from stream source to their maximum value.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @param iterable<TKey, TValue>                              $source     Stream source to reduce.
 * @param TReducedValue|null                                  $initial    Initial value to start with.
 * @param (callable(TValue, TKey): (TReducedValue|null))|null $extractor  Optional function to extract reducible value.
 * @param (callable(TReducedValue, TReducedValue): int)|null  $comparator Optional comparator.
 *
 * @return TReducedValue Maximum value.
 */
function max(iterable $source, mixed $initial = null, ?callable $extractor = null, ?callable $comparator = null): mixed
{
    return reduce($source, Reducer\Max::class, $initial, $extractor, $comparator);
}

/**
 * Reduces values from stream source to their minimum value.
 *
 * @template TKey
 * @template TValue
 * @template TReducedValue
 *
 * @param iterable<TKey, TValue>                              $source     Stream source to reduce.
 * @param TReducedValue|null                                  $initial    Initial value to start with.
 * @param (callable(TValue, TKey): (TReducedValue|null))|null $extractor  Optional function to extract reducible value.
 * @param (callable(TReducedValue, TReducedValue): int)|null  $comparator Optional comparator.
 *
 * @return TReducedValue Minimum value.
 */
function min(iterable $source, mixed $initial = null, ?callable $extractor = null, ?callable $comparator = null): mixed
{
    return reduce($source, Reducer\Min::class, $initial, $extractor, $comparator);
}

/**
 * Reduces values from stream source to their sum.
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue>                          $source    Stream source to reduce.
 * @param int|float|null                                  $initial   Initial value to start with.
 * @param (callable(TValue, TKey): (int|float|null))|null $extractor Optional function to extract reducible value.
 *
 * @return int|float|null Sum of values.
 */
function sum(iterable $source, int|float|null $initial = null, ?callable $extractor = null): int|float|null
{
    return reduce($source, Reducer\Sum::class, $initial, $extractor);
}
