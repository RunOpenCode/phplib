<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\flip;

final class FlipTest extends TestCase
{
    #[Test]
    public function flips(): void
    {
        $operator = flip([
            10 => 'a',
            15 => 'b',
            5 => 'c',
        ]);

        $this->assertSame([
            'a' => 10,
            'b' => 15,
            'c' => 5,
        ], \iterator_to_array($operator));
    }
}
