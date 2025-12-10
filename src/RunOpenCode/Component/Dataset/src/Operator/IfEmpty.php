<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * IfEmpty operator.
 *
 * IfEmpty operator tracks number of yielded items. If stream was empty, it
 * will yield provided callable and yield from it as alternative source of
 * data.
 *
 * If exception is provided instead of callable, that exception will be thrown
 * instead.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\IfEmpty;
 *
 * $at = new IfEmpty(
 *    collection: new Dataset([]),
 *    action: static fn(): iterable => new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TAlternativeKey
 * @template TAlternativeValue
 *
 * @phpstan-type ActionCallable = callable(): (iterable<TAlternativeKey, TAlternativeValue>|never)
 *
 * @extends AbstractStream<TAlternativeKey, TAlternativeValue>
 * @implements OperatorInterface<TAlternativeKey, TAlternativeValue>
 */
final class IfEmpty extends AbstractStream implements OperatorInterface
{
    private \Closure $action;

    /**
     * @param iterable<TKey|TValue>     $collection Collection to iterate over.
     * @param \Exception|ActionCallable $action     Action to execute if original stream is empty (or exception to throw).
     */
    public function __construct(
        private readonly iterable $collection,
        \Exception|callable       $action,
    ) {
        parent::__construct($collection);
        $this->action = $action instanceof \Exception ? static fn(): never => throw $action : $action(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $counter = 0;

        foreach ($this->collection as $key => $value) {
            yield $key => $value;
            $counter++;
        }

        if (0 === $counter) {
            yield from ($this->action)();
        }
    }
}
