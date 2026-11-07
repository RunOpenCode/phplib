<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\zip_shortest;

final class ZipShortestTest extends TestCase
{
    #[Test]
    public function equal_length_pairs(): void
    {
        $operator = zip_shortest([
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
    public function first_source_longer(): void
    {
        $operator = zip_shortest([
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
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
        ], $values);
    }

    #[Test]
    public function second_source_longer(): void
    {
        $operator = zip_shortest([
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
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
        ], $values);
    }

    #[Test]
    public function empty_first_source(): void
    {
        $operator = zip_shortest([], [
            'c' => 3,
            'd' => 4,
        ]);

        // @phpstan-ignore-next-line method.impossibleType
        $this->assertSame([], \iterator_to_array($operator));
    }

    #[Test]
    public function empty_second_source(): void
    {
        $operator = zip_shortest([
            'a' => 1,
            'b' => 2,
        ], []);

        // @phpstan-ignore-next-line method.impossibleType
        $this->assertSame([], \iterator_to_array($operator));
    }
}
