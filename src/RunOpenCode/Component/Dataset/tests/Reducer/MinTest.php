<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Min;

final class MinTest extends TestCase
{
    #[Test]
    public function min_value(): void
    {
        $reducer = new Min([
            'a' => null,
            'b' => 3,
            'c' => 2,
        ]);
        $this->assertSame([
            'a' => null,
            'b' => 3,
            'c' => 2,
        ], \iterator_to_array($reducer));
        $this->assertEquals(2, $reducer->value);
    }

    #[Test]
    public function max_extracted_value(): void
    {
        $reducer = new Min([
            3, 2, 1
        ], static fn(int $value, int $key): int => $value * $key);

        $this->assertSame([3, 2, 1], \iterator_to_array($reducer));
        $this->assertEquals(0, $reducer->value);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Min([])->value;
    }
}
