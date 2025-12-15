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

    #[Test]
    public function flattens_preserving_keys(): void
    {
        $operator = flatten([
            'foo' => ['a' => 2, 'b' => 3],
            'bar' => ['c' => 10, 'd' => 20],
        ], true);

        $this->assertSame([
            'a' => 2,
            'b' => 3,
            'c' => 10,
            'd' => 20,
        ], \iterator_to_array($operator));
    }
}
