<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * Buffer while operator.
 *
 * Iterates over given collection and creates a buffer of items as long as given
 * predicate function is satisfied.
 *
 * Yields created instances of {@see Buffer} for batch processing.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type TStorage = \ArrayObject<int, array{TKey, TValue}>
 * @phpstan-type PredicateCallable = callable(TBuffer, TValue=, TKey=): bool
 * @phpstan-type TBuffer = Buffer<TKey, TValue>
 *
 * @extends AbstractStream<int, TBuffer>
 * @implements OperatorInterface<int, TBuffer>
 */
final class BufferWhile extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $predicate;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param PredicateCallable      $predicate  Callable predicate function to evaluate.
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
        /** @var TStorage $items */
        $items  = new \ArrayObject();
        $buffer = new Buffer($items);

        foreach ($this->collection as $key => $value) {
            if (0 === \count($items)) {
                $items[] = [$key, $value];
                continue;
            }

            if (($this->predicate)($buffer, $value, $key)) {
                $items[] = [$key, $value];
                continue;
            }

            yield $buffer;

            /** @var TStorage $items */
            $items   = new \ArrayObject();
            $buffer  = new Buffer($items);
            $items[] = [$key, $value];
        }

        if (0 !== \count($items)) {
            yield $buffer;
        }
    }
}
