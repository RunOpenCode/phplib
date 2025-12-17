<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Reducer;

use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

/**
 * Reducer which calculates sum of values from a collection of values.
 *
 * Null values are ignored.
 *
 * @template TKey
 * @template TValue
 *
 * @phpstan-type ExtractorCallable = callable(TValue, TKey): (int|float|null)
 *
 * @implements ReducerInterface<TKey, TValue, int|float|null>
 */
final class Sum implements ReducerInterface
{
    /**
     * {@inheritdoc}
     */
    public private(set) mixed $value;

    private \Closure $extractor;

    /**
     * @param int|float|null         $initial   Initial value.
     * @param ExtractorCallable|null $extractor Optional reducible value extractor.
     */
    public function __construct(
        int|float|null $initial = null,
        ?callable      $extractor = null,
    ) {
        $this->value     = $initial;
        $this->extractor = null !== $extractor ? $extractor(...) : static fn(mixed $value): mixed => $value;
    }

    /**
     * {@inheritdoc}
     */
    public function next(mixed $value, mixed $key): void
    {
        /** @var int|float|null $value */
        $value = ($this->extractor)($value, $key);

        if (null === $value) {
            return;
        }

        if (null === $this->value) {
            $this->value = $value;
            return;
        }

        $this->value += $value;
    }
}
