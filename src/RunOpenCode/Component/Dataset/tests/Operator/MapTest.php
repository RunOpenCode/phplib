<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;

use function RunOpenCode\Component\Dataset\map;

final class MapTest extends TestCase
{
    #[Test]
    public function maps(): void
    {
        $operator = map(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ],
            static fn(int $value): int => $value * 2,
            static fn(string $key): string => \sprintf('mapped_%s', $key),
        );

        $this->assertSame([
            'mapped_a' => 2,
            'mapped_b' => 4,
            'mapped_c' => 6,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function map_keys(): void
    {
        $operator = map(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ],
            keyTransform: static fn(string $key): string => \sprintf('mapped_%s', $key),
        );

        $this->assertSame([
            'mapped_a' => 1,
            'mapped_b' => 2,
            'mapped_c' => 3,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function map_values(): void
    {
        $operator = map(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ],
            valueTransform: static fn(int $value): int => $value * 2,
        );

        $this->assertSame([
            'a' => 2,
            'b' => 4,
            'c' => 6,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function map_throws_exception_when_transform_function_missing(): void
    {
        $this->expectException(LogicException::class);

        map([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);
    }
}
