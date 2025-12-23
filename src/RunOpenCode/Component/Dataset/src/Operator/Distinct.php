<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Distinct operator.
 *
 * The distinct operator emits only items that are unique, preserving FIFO order.
 * By default, items are compared using the strict equality operator (===).
 *
 * Optionally, you may provide a callable that computes an identity for each item
 * based on its value and key. When provided, distinctness is determined by
 * performing strict equality comparisons on the computed identities instead of the
 * original items.
 *
 * WARNING: The memory consumption of this operator depends on the number of distinct
 * items emitted by the upstream. As a result, it is considered memory-unsafe, since
 * memory usage can grow without bound for unbounded streams.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Distinct;
 *
 * $distinct = new Distinct(
 *    source: ['a' => 1, 'b' => 2, 'c' => 1],
 *    identity: static fn($value, $key): string => (string) $value,
 * );
 * ```
 *
 * TODO: This operator may be refactored for comparison without identity callable.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type IdentityCallable = callable(TValue, TKey=): string
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Distinct extends AbstractStream implements OperatorInterface
{
    private readonly ?\Closure $identity;

    /**
     * @param iterable<TKey, TValue> $source   Stream source to iterate over.
     * @param IdentityCallable|null  $identity User defined callable to determine item identity. If null, strict comparison (===) of values is used.
     */
    public function __construct(
        private readonly iterable $source,
        ?callable                 $identity = null,
    ) {
        parent::__construct($this->source);
        $this->identity = $identity ? $identity(...) : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        yield from null !== $this->identity ? $this->identifiable() : $this->generic();
    }

    /**
     * @return iterable<TKey, TValue>
     */
    private function identifiable(): iterable
    {
        $identities = [];

        \assert(null !== $this->identity);

        foreach ($this->source as $key => $item) {
            $identity = ($this->identity)($item, $key);

            if (isset($identities[$identity])) {
                continue;
            }

            $identities[$identity] = true;

            yield $key => $item;
        }
    }

    /**
     * @return iterable<TKey, TValue>
     */
    private function generic(): iterable
    {
        $emitted = [];

        foreach ($this->source as $key => $item) {
            if (\in_array($item, $emitted, true)) {
                continue;
            }

            $emitted[] = $item;

            yield $key => $item;
        }
    }
}
