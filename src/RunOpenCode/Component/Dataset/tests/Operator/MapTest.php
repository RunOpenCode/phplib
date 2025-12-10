<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\map;

final class MapTest extends TestCase
{
    #[Test]
    public function maps(): void
    {
        $operator = map(
            [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ],
            static fn(int $value): int => $value * 2,
            static fn(string $key): string => \sprintf('mapped_%s', $key),
        );

        $this->assertSame([
            'mapped_a' => 2,
            'mapped_b' => 4,
            'mapped_c' => 6,
        ], \iterator_to_array($operator));
    }
}
