<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Count;

final class CountTest extends TestCase
{
    #[Test]
    public function counts_everything(): void
    {
        $reducer = new Count([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], \iterator_to_array($reducer));
        $this->assertSame(3, $reducer->value);
    }

    #[Test]
    public function counts_filtered_only(): void
    {
        $reducer = new Count([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], static fn(int $value, string $key): bool => $key !== 'b' && $value !== 1);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], \iterator_to_array($reducer));
        $this->assertSame(1, $reducer->value);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Count([])->value;
    }
}
