<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Min;

use function RunOpenCode\Component\Dataset\reduce;

final class MinTest extends TestCase
{
    #[Test]
    public function min_value(): void
    {
        $dataset = [
            'a' => null,
            'b' => 3,
            'c' => 2,
        ];

        $this->assertEquals(
            2,
            reduce($dataset, Min::class),
        );
    }

    #[Test]
    public function max_extracted_value(): void
    {
        $dataset = [3, 2, 1];

        $this->assertEquals(
            0,
            reduce($dataset, Min::class, extractor: static fn(int $value, int $key): int => $value * $key),
        );
    }
}
