<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\pairwise;

final class PairwiseTest extends TestCase
{
    #[Test]
    public function yields_nothing_for_empty_source(): void
    {
        $operator = pairwise([]);

        // @phpstan-ignore-next-line method.impossibleType
        $this->assertSame([], \iterator_to_array($operator));
    }

    #[Test]
    public function yields_nothing_for_single_item_source(): void
    {
        $operator = pairwise(['a' => 1]);

        // @phpstan-ignore-next-line method.impossibleType
        $this->assertSame([], \iterator_to_array($operator));
    }

    #[Test]
    public function yields_single_pair_for_two_items(): void
    {
        $operator = pairwise([
            'a' => 1,
            'b' => 2,
        ]);

        $keys   = [];
        $values = [];

        foreach ($operator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }

        $this->assertSame([
            ['a', 'b'],
        ], $keys);

        $this->assertSame([
            [1, 2],
        ], $values);
    }

    #[Test]
    public function yields_overlapping_consecutive_pairs_for_multiple_items(): void
    {
        $operator = pairwise([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ]);


        $keys   = [];
        $values = [];

        foreach ($operator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }

        $this->assertSame([
            ['a', 'b'],
            ['b', 'c'],
            ['c', 'd'],
        ], $keys);

        $this->assertSame([
            [1, 2],
            [2, 3],
            [3, 4],
        ], $values);
    }
}
