<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Tap operator.
 *
 * Allows to "tap into" the iteration loop and execute a callback for each item in the collection without modifying
 * the items themselves.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Tap;
 * use RunOpenCode\Component\Dataset\Dataset;
 *
 * $tap = new Tap(
 *   collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *   spy: static fn(int $value, string $key): void => print("Key: $key, Value: $value\n"),
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type TapCallable = callable(TValue, TKey): void
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Tap extends AbstractStream implements OperatorInterface
{
    private \Closure $callback;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param TapCallable            $callback   User defined callable to execute for each item.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $callback,
    ) {
        parent::__construct($this->collection);
        $this->callback = $callback(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->collection as $key => $value) {
            ($this->callback)($value, $key);

            yield $key => $value;
        }
    }
}
