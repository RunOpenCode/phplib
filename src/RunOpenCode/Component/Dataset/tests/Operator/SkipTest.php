<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Operator\Skip;

final class SkipTest extends TestCase
{
    #[Test]
    public function skips(): void
    {
        $operator = new Skip([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ], 2);

        $this->assertSame([
            'c' => 3,
            'd' => 4,
        ], \iterator_to_array($operator));
    }
}
