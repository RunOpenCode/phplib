<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\values;

final class ValuesTest extends TestCase
{
    #[Test]
    public function values(): void
    {
        $operator = values([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ]);

        $this->assertSame([
            0 => 'a',
            1 => 'b',
            2 => 'c',
        ], \iterator_to_array($operator));
    }
}
