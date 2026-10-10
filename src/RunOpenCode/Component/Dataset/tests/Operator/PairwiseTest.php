<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\pairwise;

final class PairwiseTest extends TestCase
{
    #[Test]
    public function equal_length_pairs(): void
    {
        $operator = pairwise([
            'a' => 1,
            'b' => 2,
        ], [
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
            ['a', 'c'],
            ['b', 'd'],
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
        ], $values);
    }

    #[Test]
    public function first_pair_longer(): void
    {
        $operator = pairwise([
            'a' => 1,
            'b' => 2,
            'f' => 5,
        ], [
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
            ['a', 'c'],
            ['b', 'd'],
            ['f', null],
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
            [5, null],
        ], $values);
    }

    #[Test]
    public function second_pair_longer(): void
    {
        $operator = pairwise([
            'a' => 1,
            'b' => 2,
        ], [
            'c' => 3,
            'd' => 4,
            'f' => 5,
        ]);

        $keys   = [];
        $values = [];

        foreach ($operator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }

        $this->assertSame([
            ['a', 'c'],
            ['b', 'd'],
            [null, 'f'],
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
            [null, 5],
        ], $values);
    }
}
