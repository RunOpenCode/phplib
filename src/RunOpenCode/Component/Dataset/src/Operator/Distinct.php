<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Distinct operator.
 *
 * Distinct operator can be used in two modes, with identity callable or without it.
 *
 * When identity callable is provided, it is used to determine the identity of each
 * item in the collection. If two items have the same identity, only the first one
 * is yielded.
 *
 * When identity callable is not provided, strict comparison (===) is used to determine
 * if two items are the same.
 *
 * WARNING: Memory consumption of this operator depends on the number of distinct items
 * and type of identity callable used. If the collection has a lot of distinct items,
 * or if the identity callable produces a lot of unique identities, memory consumption
 * can grow significantly. Do note that if value based comparison is used, memory consumption
 * can grow even more, as all distinct values need to be stored in memory.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Distinct;
 *
 * $distinct = new Distinct(
 *    collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 1]),
 *    identity: static fn($value, $key): string => (string) $value,
 * );
 * ```
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
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param IdentityCallable|null  $identity   User defined callable to determine item identity. If null, strict comparison (===) is used.
     */
    public function __construct(
        private readonly iterable $collection,
        ?callable                 $identity = null,
    ) {
        parent::__construct($this->collection);
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

        foreach ($this->collection as $key => $item) {
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

        foreach ($this->collection as $key => $item) {
            if (\in_array($item, $emitted, true)) {
                continue;
            }

            $emitted[] = $item;

            yield $key => $item;
        }
    }
}
