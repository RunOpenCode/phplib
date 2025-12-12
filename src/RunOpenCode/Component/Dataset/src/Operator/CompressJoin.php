<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Compress join operator.
 *
 * Compress join operator iterates over given collection and compresses items based on the predicate and join functions.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\CompressJoin;
 *
 * $compressJoin = new CompressJoin(
 *   collection: new Dataset([1 => [1, 2], 2 => [1, 3], 3 => [1, 4], 4 => [2, 1], 5 => [2, 2]]),
 *   predicate: static fn(array $values): bool => $values[0][0] === $values[1][0],
 *   join: static fn(array $buffer): iterable => [
 *     $buffer[0][1][0] => array_map(static fn(array $record): int => $record[1][1], $buffer)
 *   ],
 * );
 * // $compressJoin will yield [1 => [2, 3, 4], 2 => [1, 2]]
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 * @template TModifiedValue
 *
 * @phpstan-type PredicateValues = array{TValue, TValue}
 * @phpstan-type PredicateKeys   = array{TKey,  TKey}
 * @phpstan-type Record = array{TKey, TValue}
 * @phpstan-type Buffer = list<Record>
 * @phpstan-type PredicateCallable = callable(PredicateValues, PredicateKeys=, Buffer=): bool
 * @phpstan-type JoinCallable = callable(Buffer): iterable<TModifiedKey, TModifiedValue>
 *
 * @extends AbstractStream<TModifiedKey, TModifiedValue>
 * @implements OperatorInterface<TModifiedKey, TModifiedValue>
 */
final class CompressJoin extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $predicate;

    private readonly \Closure $join;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param PredicateCallable      $predicate  Callable predicate function to evaluate.
     * @param JoinCallable           $join       Callable join function to produce joined records.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $predicate,
        callable                  $join,
    ) {
        parent::__construct($collection);
        $this->predicate = $predicate(...);
        $this->join      = $join(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        /** @var Buffer $buffer */
        $buffer = [];
        /** @var Record|null $previous */
        $previous = null;

        foreach ($this->collection as $key => $value) {
            if (0 === \count($buffer)) {
                $previous = [$key, $value];
                $buffer[] = $previous;
                continue;
            }

            \assert(null !== $previous);

            if (($this->predicate)([$previous[1], $value], [$previous[0], $key], $buffer)) {
                $previous = [$key, $value];
                $buffer[] = $previous;
                continue;
            }

            yield from ($this->join)($buffer);
            $previous = [$key, $value];
            $buffer   = [$previous];
        }

        if (0 !== \count($buffer)) {
            yield from ($this->join)($buffer);
        }
    }
}
