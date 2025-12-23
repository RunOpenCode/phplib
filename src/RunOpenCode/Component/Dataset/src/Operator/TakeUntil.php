<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Take until operator.
 *
 * The take operator processes a stream source and yields items until the predicate
 * callable indicates that iteration should stop.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Take;
 *
 * $takeUntil = new TakeUntil(
 *   collection: ['a' => 1, 'b' => 2, 'c' => 3],
 *   predicate: static fn(int $value, string $key): bool => $value > 2,
 * );
 * // $takeUntil will yield ['a' => 1, 'b' => 2]
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type PredicateCallable = callable(TValue, TKey=): bool
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class TakeUntil extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $predicate;

    /**
     * @param iterable<TKey, TValue> $source    Stream source to iterate over.
     * @param callable               $predicate Predicate callable to evaluate stop condition.
     */
    public function __construct(
        private readonly iterable $source,
        callable                  $predicate,
    ) {
        parent::__construct($source);
        $this->predicate = $predicate(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $key => $value) {
            if (($this->predicate)($value, $key, $this->source)) {
                break;
            }

            yield $key => $value;
        }
    }
}
