<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;
use RunOpenCode\Component\Dataset\Model\Buffer;

/**
 * WithAll operator.
 *
 * Iterates over the given stream source and yields its items. During iteration
 * process, every yielded item is stored into a buffer.
 *
 * When iteration completes the finalization function is invoked providing the
 * collected items as argument.
 *
 * If iteration of the stream source ends prematurely (for example, via a `break`
 * statement), the finalization function is invoked when the operator instance
 * is garbage-collected with the items that were collected up to that point.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\WithAll;
 * use RunOpenCode\Component\Dataset\Model\Buffer;
 *
 * $withAll = new WithAll(
 *    source: ['a' => 1, 'b' => 2, 'c' => 3],
 *    finalizer: static fn(Buffer $buffer): void => // finalization logic,
 * );
 * ```
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type FinalizerCallable = callable(Buffer<TKey, TValue>): void
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class WithAll extends AbstractStream implements OperatorInterface
{
    private \Closure $finalizer;

    private bool $finalized = false;

    /**
     * @var list<array{TKey, TValue}>
     */
    private array $collected = [];

    /**
     * @param iterable<TKey, TValue> $source    Stream source to iterate over.
     * @param FinalizerCallable      $finalizer Callable function to be invoked when iteration completes or is interrupted.
     */
    public function __construct(
        private readonly iterable $source,
        callable                  $finalizer,
    ) {
        parent::__construct($source);
        $this->finalizer = $finalizer(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $key => $item) {
            $this->collected[] = [$key, $item];

            yield $key => $item;
        }

        $this->finalize();
    }

    private function finalize(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->finalized = true;

        ($this->finalizer)(new Buffer(new \ArrayObject($this->collected)));

        $this->collected = [];
    }

    public function __destruct()
    {
        $this->finalize();
    }
}
