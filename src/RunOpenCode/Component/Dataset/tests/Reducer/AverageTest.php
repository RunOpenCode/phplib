<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Average;

final class AverageTest extends TestCase
{
    #[Test]
    public function average_value(): void
    {
        $reducer = new Average([
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ]);

        $this->assertSame([
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ], \iterator_to_array($reducer));
        $this->assertEqualsWithDelta(5.0, $reducer->value, 0.0001);
    }

    #[Test]
    public function average_from_extracted_value(): void
    {
        $reducer = new Average([
            'a' => [2],
            'b' => [4],
            'c' => [6],
            'd' => [null],
            'e' => [8],
        ], static fn(array $item): ?int => $item[0]);

        $this->assertSame([
            'a' => [2],
            'b' => [4],
            'c' => [6],
            'd' => [null],
            'e' => [8],
        ], \iterator_to_array($reducer));
        $this->assertEqualsWithDelta(5.0, $reducer->value, 0.0001);
    }

    #[Test]
    public function average_skips_nulls(): void
    {
        $reducer = new Average([
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ], countNull: true);

        $this->assertSame([
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ], \iterator_to_array($reducer));
        $this->assertEqualsWithDelta(4.0, $reducer->value, 0.0001);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Average([])->value;
    }
}
