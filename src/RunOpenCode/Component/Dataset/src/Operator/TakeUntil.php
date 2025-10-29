<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Take until operator.
 *
 * Take operator iterates over given collection and yields only the first N items
 * until condition is met.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Take;
 *
 * $takeUntil = new TakeUntil(
 *   collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *   predicate: static fn(int $value, string $key): bool => $value > 2,
 * );
 * // $takeUntil will yield ['a' => 1, 'b' => 2]
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type PredicateCallable = callable(TValue, TKey): bool
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class TakeUntil extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $predicate;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param callable               $predicate  Callable predicate function to evaluate.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $predicate,
    ) {
        parent::__construct($collection);
        $this->predicate = $predicate(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $key => $value) {
            if (($this->predicate)($value, $key, $this->collection)) {
                break;
            }

            yield $key => $value;
        }
    }
}
