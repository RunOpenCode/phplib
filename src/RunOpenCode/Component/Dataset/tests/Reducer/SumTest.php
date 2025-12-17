<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\reduce;

final class SumTest extends TestCase
{
    #[Test]
    public function sums_values(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 2,
            'c' => null,
        ];

        $this->assertEquals(
            3,
            reduce($dataset, Sum::class),
        );
    }

    #[Test]
    public function sums_extracted_values(): void
    {
        $dataset = [1, 2, 3];

        $this->assertEquals(
            8,
            reduce($dataset, Sum::class, extractor: static fn(int $value, int $key): int => $value * $key),
        );
    }
}
