<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\sort;

final class SortTest extends TestCase
{
    #[Test]
    public function sorts_by_value_without_comparator(): void
    {
        $operator = sort([
            'a' => 3,
            'b' => 1,
            'c' => 2,
        ]);

        $this->assertSame([
            'b' => 1,
            'c' => 2,
            'a' => 3,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function sorts_by_key_without_comparator(): void
    {
        $operator = sort([
            'c' => 3,
            'a' => 1,
            'b' => 2,
        ], byKeys: true);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function sorts_by_value_with_comparator(): void
    {
        $operator = sort([
            'a' => 3,
            'b' => 1,
            'c' => 2,
        ], comparator: static fn(int $first, int $second): int => $second * 2 <=> $first);

        $this->assertSame([
            'c' => 2,
            'a' => 3,
            'b' => 1,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function sorts_by_key_with_comparator(): void
    {
        $operator = sort(
            [
            'bar' => 30,
            'foo' => 100,
            'baz' => 2,
        ],
            comparator: static fn(string $first, string $second): int => -1 * \strcmp($second, $first),
            byKeys: true
        );

        $this->assertSame([
            'bar' => 30,
            'baz' => 2,
            'foo' => 100,
        ], \iterator_to_array($operator));
    }
}
