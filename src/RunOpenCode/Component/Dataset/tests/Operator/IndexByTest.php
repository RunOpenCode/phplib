<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\index_by;

final class IndexByTest extends TestCase
{
    #[Test]
    public function index_by(): void
    {
        $operator = index_by(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ],
            static fn(int $value): int => $value * 2,
        );

        $this->assertSame([
            2 => 1,
            4 => 2,
            6 => 3,
        ], \iterator_to_array($operator));
    }
}
