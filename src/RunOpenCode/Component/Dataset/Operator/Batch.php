<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Batch operator.
 *
 * Batch operator collects items from source iterable into batches of given
 * size and passes them to user defined callable.
 *
 * User defined callable receives two arguments:
 * - array of records (each record is a two item array where first item is key and second item is value)
 * - current batch number (starting from 1)
 *
 * User may modify mutate batch items, filter them out or even add new items.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Batch;
 *
 * $batch = new Batch(
 *    collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]),
 *    size: 2,
 *    onBatch: function(array $batch, int $batchNumber): iterable {
 *       foreach ($batch as [$key, $value]) {
 *          // Do some operations on $key and $value,
 *          // e.g. load additional data from database
 *          // for each record.
 *          yield $key => $value;
 *      }
 *   }
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TModifiedKey
 * @template TModifiedValue
 *
 * @phpstan-type Record = array{TKey, TValue}
 * @phpstan-type Records = Record[]
 *
 * @phpstan-type OnBatchCallable = callable(Records $batch, int $batchNumber): iterable<TModifiedKey, TModifiedValue>
 *
 * @extends AbstractStream<TModifiedKey, TModifiedValue>
 * @implements OperatorInterface<TModifiedKey, TModifiedValue>
 */
final class Batch extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $onBatch;

    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param OnBatchCallable        $onBatch    User defined callable to be called on each batch.
     * @param positive-int           $size       Size of the batch buffer.
     */
    public function __construct(
        private readonly iterable $collection,
        callable                  $onBatch,
        private readonly int      $size = 1000,
    ) {
        parent::__construct($collection);
        $this->onBatch = $onBatch(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $batch     = [];
        $iteration = 1;

        foreach ($this->collection as $key => $value) {
            $batch[] = [$key, $value];

            if (\count($batch) === $this->size) {
                yield from ($this->onBatch)($batch, $iteration);
                $batch = [];
                $iteration++;
            }
        }

        if (\count($batch) !== 0) {
            yield from ($this->onBatch)($batch, $iteration);
        }
    }
}
