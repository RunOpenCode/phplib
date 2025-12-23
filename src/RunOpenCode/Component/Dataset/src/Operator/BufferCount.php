<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * Buffer count operator.
 *
 * Buffers the stream of data until buffer reaches predefined number of items (or
 * stream is exhausted) and yields instance of {@see Buffer}.
 *
 * Memory consumption depends on the size of the buffer; however, the operator is
 * still considered memory-safe.
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
     * @param iterable<TKey, TValue> $source Stream source to iterate over.
     * @param positive-int           $count  Number of items to store into buffer.
     */
    public function __construct(
        private readonly iterable $source,
        private readonly int      $count = 1000,
    ) {
        parent::__construct($source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        /** @var \ArrayObject<int, array{TKey, TValue}> $items */
        $items = new \ArrayObject();

        foreach ($this->source as $key => $value) {
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
