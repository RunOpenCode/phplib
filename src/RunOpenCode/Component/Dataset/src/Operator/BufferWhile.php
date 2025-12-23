<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * Buffer while operator.
 *
 * Iterates over given data stream and creates a buffer of items as long as given
 * predicate function is satisfied.
 *
 * Yields created instances of {@see Buffer} for batch processing.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type TStorage = \ArrayObject<int, array{TKey, TValue}>
 * @phpstan-type TBuffer = Buffer<TKey, TValue>
 * @phpstan-type PredicateCallable = callable(TBuffer, TValue=, TKey=): bool
 *
 * @extends AbstractStream<int, TBuffer>
 * @implements OperatorInterface<int, TBuffer>
 */
final class BufferWhile extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $predicate;

    /**
     * @param iterable<TKey, TValue> $source    Stream source to iterate over.
     * @param PredicateCallable      $predicate Predicate function to evaluate if current item should be placed into existing buffer, or
     *                                          existing buffer should be yielded and new one should be created with current item.
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
        /** @var TStorage $items */
        $items  = new \ArrayObject();
        $buffer = new Buffer($items);

        foreach ($this->source as $key => $value) {
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
