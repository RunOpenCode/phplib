<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * Buffer count operator.
 *
 * Iterates over given collection and creates a buffer of items with number of items
 * up to given capacity.
 *
 * Yields created instances of {@see Buffer} for batch processing.
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<int, Buffer<TKey, TValue>>
 * @implements OperatorInterface<int, Buffer<TKey, TValue>>
 */
final class BufferCount extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param positive-int           $count      How many items to buffer.
     */
    public function __construct(
        private readonly iterable $collection,
        private readonly int      $count = 1000,
    ) {
        parent::__construct($collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        /** @var \ArrayObject<int, array{TKey, TValue}> $items */
        $items = new \ArrayObject();

        foreach ($this->collection as $key => $value) {
            $items[] = [$key, $value];

            if (\count($items) === $this->count) {
                yield new Buffer($items);
                $items = new \ArrayObject();
            }
        }

        if (\count($items) !== 0) {
            yield new Buffer($items);
        }
    }
}
