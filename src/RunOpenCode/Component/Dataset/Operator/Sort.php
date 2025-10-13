<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Sort operator.
 *
 * Sort operator iterates over given collection and yields items sorted by keys or values.
 * User may provide custom comparator function or use default one, which uses spaceship
 * operator (<=>) on values or keys (defined by user).
 *
 * Keys are preserved.
 *
 * WARNING: this is not memory efficient operator.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Sort;
 *
 * $sortByValues = new Sort(
 *   collection: new Dataset(['a' => 3, 'b' => 1, 'c' => 2]),
 *   comparator: static fn(int $first, int $second): int => $first <=> $second,
 *   byKeys: false,
 * );
 *
 * $sortByKeys = new Sort(
 *   collection: new Dataset(['a' => 3, 'b' => 1, 'c' => 2]),
 *   comparator: static fn(string $first, string $second): int => \strcmp($first, $second),
 *   byKeys: true,
 * );
 *
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type KeyComparator = callable(TKey, TKey): int
 * @phpstan-type ValueComparator = callable(TValue, TValue): int
 * @phpstan-type Comparator = KeyComparator|ValueComparator
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Sort extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $sorter;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param Comparator|null        $comparator User defined callable to compare two items. If null, spaceship operator (<=>) is used.
     * @param bool                   $byKeys     If `byKeys` is true, keys will be compared instead of values.
     */
    public function __construct(
        private readonly iterable $collection,
        ?callable                 $comparator = null,
        private readonly bool     $byKeys = false,
    ) {
        parent::__construct($this->collection);
        $this->sorter = $comparator ? $comparator(...) : static fn(mixed $first, mixed $second): int => $first <=> $second;
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $items    = [];
        $getValue = fn(array $item): mixed => $this->byKeys ? $item[0] : $item[1];

        foreach ($this->collection as $key => $value) {
            $items[] = [$key, $value];
        }

        \uasort($items, fn($first, $second): int => ($this->sorter)(
            $getValue($first),
            $getValue($second)
        ));

        foreach ($items as [$key, $value]) {
            yield $key => $value;
        }
    }
}
