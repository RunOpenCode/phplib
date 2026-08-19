<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\keys;

final class KeysTest extends TestCase
{
    #[Test]
    public function keys(): void
    {
        $operator = keys([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ]);

        $this->assertSame([
            0 => 10,
            1 => 15,
            2 => 5,
        ], \iterator_to_array($operator));
    }
}
