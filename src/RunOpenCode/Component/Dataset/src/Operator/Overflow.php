<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Overflow operator.
 *
 * Overflow operator tracks number of yielded items and throws exception if
 * stream produced more than defined number of allowed items.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Overflow;
 *
 * $at = new Overflow(
 *    collection: new Dataset(['a' => 1, 'b' => 2, 'c' => 3]),
 *    capacity: 2,
 *    exception: new \Exception('Max number of items exceeded.'),
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class Overflow extends AbstractStream implements OperatorInterface
{
    /**
     * @param iterable<TKey, TValue> $collection Collection to iterate over.
     * @param positive-int           $capacity Max number of items to iterate over.
     * @param \Exception|null        $exception Which exception to throw if collection has more then allowed items ({@see \OverflowException} by default).
     */
    public function __construct(
        private readonly iterable    $collection,
        private readonly int         $capacity,
        private readonly ?\Exception $exception = null,
    ) {
        parent::__construct($collection);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        $counter = 0;

        foreach ($this->collection as $key => $value) {
            if ($counter >= $this->capacity) {
                throw $this->exception ?? new \OverflowException(\sprintf(
                    'Defined capacity of %d items exceeded.',
                    $this->capacity
                ));
            }

            yield $key => $value;

            $counter++;
        }
    }
}
