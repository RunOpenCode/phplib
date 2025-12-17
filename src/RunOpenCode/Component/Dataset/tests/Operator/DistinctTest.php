<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\distinct;

final class DistinctTest extends TestCase
{
    #[Test]
    public function distinct_by_identity(): void
    {
        $operator = distinct([
            'a' => [2],
            'b' => [10],
            'c' => [2],
            'd' => [10],
        ], static fn(array $value, string $key): string => (string)$value[0]); // @phpstan-ignore-line

        $this->assertSame([
            'a' => [2],
            'b' => [10],
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function distinct_by_value(): void
    {
        $operator = distinct([
            'a' => 2,
            'b' => 10,
            'c' => 2,
            'd' => 10,
        ]);

        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], \iterator_to_array($operator));
    }
}
