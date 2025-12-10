<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Max;

final class MaxTest extends TestCase
{
    #[Test]
    public function max_value(): void
    {
        $reducer = new Max([
            'a' => 1,
            'b' => 3,
            'c' => null,
        ]);
        $this->assertSame([
            'a' => 1,
            'b' => 3,
            'c' => null,
        ], \iterator_to_array($reducer));
        $this->assertEquals(3, $reducer->value);
    }

    #[Test]
    public function max_extracted_value(): void
    {
        $reducer = new Max([
            3, 2, 1
        ], static fn(int $value, int $key): int => $value * $key);

        $this->assertSame([3, 2, 1], \iterator_to_array($reducer));
        $this->assertEquals(2, $reducer->value);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Max([])->value;
    }
}
