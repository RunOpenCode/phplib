<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\reduce;

final class CallbackTest extends TestCase
{
    #[Test]
    public function reduces(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->assertEqualsWithDelta(
            6,
            reduce($dataset, static fn(int $carry, int $value): int => $carry + $value, initial: 0),
            0.0001
        );
    }
}
