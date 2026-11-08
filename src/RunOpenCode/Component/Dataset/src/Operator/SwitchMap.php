<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Switch map operator.
 *
 * The switch map operator processes a stream source and yields items from the inner iterable
 * returned by the mapper callable for each item in the source.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\SwitchMap;
 *
 * $switchMap = new SwitchMap(
 *   collection: ['a' => 1, 'b' => 2, 'c' => 3],
 *   mapper: static fn(int $value, string $key): iterable => \range(0, $value),
 * );
 * // $switchMap will yield [0, 1, 0, 1, 2, 0, 1, 2, 3]
 * ```
 *
 * @template TKey
 * @template TValue
 * @template TInnerKey
 * @template TInnerValue
 *
 * @phpstan-type MapperCallable = callable(TValue, TKey=): iterable<TInnerKey, TInnerValue>
 *
 * @extends AbstractStream<TKey, TValue>
 * @implements OperatorInterface<TKey, TValue>
 */
final class SwitchMap extends AbstractStream implements OperatorInterface
{
    private readonly \Closure $mapper;

    /**
     * @param iterable<TKey, TValue> $source Stream source to iterate over.
     * @param callable               $mapper Mapper callable to transform each item.
     */
    public function __construct(
        private readonly iterable $source,
        callable                  $mapper,
    ) {
        parent::__construct($source);
        $this->mapper = $mapper(...);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        foreach ($this->source as $key => $value) {
            yield from ($this->mapper)($value, $key, $this->source);
        }
    }
}
