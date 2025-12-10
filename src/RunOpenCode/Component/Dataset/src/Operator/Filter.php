<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Filter operator.
 *
 * Filter operator iterates over given collection and yields only those items
 * for which user defined callable returns true.
 *
 * User defined callable receives three arguments:
 * - current item value
 * - current item key
 * - original collection being iterated
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Filter;
 *
 * $filter = new Filter(
 *    collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *    filter: static fn(int $value, string $key): bool => $value > 1,
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type FilterCallable = callable(TValue, TKey): bool
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Filter extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $filter;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param FilterCallable         $filter     User defined callable to filter items.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $filter,
    ) {
        parent::__construct($this->collection);
        $this->filter = $filter(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $key => $value) {
            if (!($this->filter)($value, $key)) {
                continue;
            }

            yield $key => $value;
        }
    }
}
