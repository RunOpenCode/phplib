<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Operator;

use RunOpenCode\Component\Dataset\AbstractStream;
use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

/**
 * Zip operator.
 *
 * Iterates over two streaming sources in parallel, yielding pairs of keys and values from both sources.
 *
 * If size of both sources is not equal, exception will be thrown, indicating that the sources are not of equal length.
 *
 * Example usage:
 *
 * ```php
 * use RunOpenCode\Component\Dataset\Operator\Zip;
 *
 * $merged = new Zip(
 *   first: ['a' => 1, 'b' => 2],
 *   second: ['c' => 3, 'd' => 4],
 * );
 *
 * // The resulting sequence will be:
 * ['a', 'c'] => [1, 3]
 * ['b', 'd'] => [2, 4]
 * ```
 *
 * @template TKey1
 * @template TValue1
 * @template TKey2
 * @template TValue2
 *
 * @extends AbstractStream<array{TKey1|null,TKey2|null}, array{TValue1|null,TValue2|null}>
 * @implements OperatorInterface<array{TKey1|null,TKey2|null}, array{TValue1|null,TValue2|null}>
 */
final class Zip extends AbstractStream implements OperatorInterface
{
    /**
     * @var iterable<array{TKey1|null,TKey2|null}, array{TValue1|null,TValue2|null}>
     */
    private iterable $source;

    /**
     * @param iterable<TKey1,TValue1>       $first  First source to iterate over.
     * @param iterable<TKey2,TValue2>       $second Second source to iterate over.
     * @param 'strict'|'longest'|'shortest' $mode   Mode of operation. Can be either 'strict', 'longest', or 'shortest'. Defaults to 'strict'.
     */
    public function __construct(
        private readonly iterable $first,
        private readonly iterable $second,
        private readonly string   $mode = 'strict',
    ) {
        $this->source = match ($this->mode) {
            'strict' => new ZipStrict($this->first, $this->second),
            'longest' => new ZipLongest($this->first, $this->second),
            'shortest' => new ZipShortest($this->first, $this->second),
        };

        parent::__construct($this->source);
    }

    /**
     * {@inheritdoc}
     */
    protected function iterate(): \Traversable
    {
        yield from $this->source;
    }
}
