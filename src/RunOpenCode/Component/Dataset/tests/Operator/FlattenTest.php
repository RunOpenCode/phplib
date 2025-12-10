<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\flatten;

final class FlattenTest extends TestCase
{
    #[Test]
    public function flattens(): void
    {
        $operator = flatten([
            'a' => [2, 3],
            'b' => [10, 20],
            'c' => [5],
            'd' => [1, 4, 6],
        ]);

        $this->assertSame([
            2,
            3,
            10,
            20,
            5,
            1,
            4,
            6,
        ], \iterator_to_array($operator));
    }
}
