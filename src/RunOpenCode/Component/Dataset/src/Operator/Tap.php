<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Tap operator.
 *
 * Provides a way to observe the stream by executing a callback for each item.
 *
 * Example usage:
 *
 * ```php
 *
 * use RunOpenCode\Component\Dataset\Operator\Tap;
 *
 * $tap = new Tap(
 *   collection: ['a' => 1, 'b' => 2, 'c' => 3],
 *   spy: static fn(int $value, string $key): void => print("Key: $key, Value: $value\n"),
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type TapCallable = callable(TValue, TKey=): void
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Tap extends AbstractStream implements OperatorInterface
{
    private \Closure $callback;

    /**
     * @param iterable<TKey, TValue> $source   Stream source to iterate over.
     * @param TapCallable            $callback Callable to execute for each item.
     */
    public function __construct(
        private readonly iterable $source,
        callable                  $callback,
    ) {
        parent::__construct($this->source);
        $this->callback = $callback(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $key => $value) {
            ($this->callback)($value, $key);

            yield $key => $value;
        }
    }
}
