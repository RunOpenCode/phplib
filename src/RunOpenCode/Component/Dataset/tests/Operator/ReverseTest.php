<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Operator\Reverse;

final class ReverseTest extends TestCase
{
    #[Test]
    public function reverses(): void
    {
        $operator = new Reverse([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        $this->assertSame([
            'c' => 3,
            'b' => 2,
            'a' => 1,
        ], \iterator_to_array($operator));
    }
}
