<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Operator\TakeUntil;

final class TakeUntilTest extends TestCase
{
    #[Test]
    public function takes_until(): void
    {
        $operator = new TakeUntil([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ], static fn(int $value, $key): bool => $value >= 3);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
        ], \iterator_to_array($operator));
    }
}
