<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\merge;

final class MergeTest extends TestCase
{
    #[Test]
    public function merges(): void
    {
        $operator = merge([
            'a' => 1,
            'b' => 2,
        ], [
            'c' => 3,
            'd' => 4,
        ]);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ], \iterator_to_array($operator));
    }
}
