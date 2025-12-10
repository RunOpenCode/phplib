<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Callback;

final class CallbackTest extends TestCase
{
    #[Test]
    public function reduces(): void
    {
        $reducer = new Callback([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], static fn(int $carry, int $value): int => $carry + $value, 0);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], \iterator_to_array($reducer));
        $this->assertEqualsWithDelta(6, $reducer->value, 0.0001);
    }

    #[Test]
    public function get_value_throws_exception_when_not_iterated(): void
    {
        $this->expectException(LogicException::class);

        new Callback([], static fn(): int => 0)->value;
    }
}
