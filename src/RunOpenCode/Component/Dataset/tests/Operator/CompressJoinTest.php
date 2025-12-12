<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Operator\CompressJoin;

final class CompressJoinTest extends TestCase
{
    #[Test]
    public function compress_join(): void
    {
        $operator = new CompressJoin(
            [
                1 => [10, 2],
                2 => [10, 3],
                3 => [10, 4],
                4 => [20, 1],
                5 => [20, 2],
                6 => [30, 5],
            ],
            static fn(array $values): bool => $values[0][0] === $values[1][0],
            static fn(array $buffer): iterable => [
                $buffer[0][1][0] => \array_map(static fn(array $record): int => $record[1][1], $buffer),
            ],
        );

        $this->assertSame([
            10 => [2, 3, 4],
            20 => [1, 2],
            30 => [5],
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function compress_join_with_single_element(): void
    {
        $operator = new CompressJoin(
            [
                1 => [10, 2],
            ],
            static fn(array $values): bool => $values[0][0] === $values[1][0], // @phpstan-ignore-line
            static fn(array $buffer): iterable => [
                $buffer[0][1][0] => \array_map(static fn(array $record): int => $record[1][1], $buffer),
            ],
        );

        $this->assertSame([
            10 => [2],
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function compress_join_with_empty(): void
    {
        $operator = new CompressJoin(
            [],
            static fn(array $values): bool => $values[0][0] === $values[1][0],
            static fn(array $buffer): iterable => [
                $buffer[0][1][0] => \array_map(static fn(array $record): int => $record[1][1], $buffer), //@phpstan-ignore-line
            ],
        );

        $this->assertSame([], \iterator_to_array($operator));
    }
}