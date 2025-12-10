<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Operator\Take;

final class TakeTest extends TestCase
{
    #[Test]
    public function takes(): void
    {
        $operator = new Take([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], 2);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
        ], \iterator_to_array($operator));
    }
}
