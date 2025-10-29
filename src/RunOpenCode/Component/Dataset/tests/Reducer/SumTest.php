<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Sum;

final class SumTest extends TestCase
{
    #[Test]
    public function sums_values(): void
    {
        $reducer = new Sum([
            'a' => 1,
            'b' => 2,
            'c' => null,
        ]);

        $this->assertEquals([
            'a' => 1,
            'b' => 2,
            'c' => null,
        ], \iterator_to_array($reducer));
        $this->assertEquals(3, $reducer->value);
    }

    #[Test]
    public function sums_extracted_values(): void
    {
        $reducer = new Sum([1, 2, 3], static fn(int $value, int $key): int => $value * $key);

        $this->assertSame([1, 2, 3], \iterator_to_array($reducer));
        $this->assertEquals(8, $reducer->value);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Sum([])->value;
    }
}
