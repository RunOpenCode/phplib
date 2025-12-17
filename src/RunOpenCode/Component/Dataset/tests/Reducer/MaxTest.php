<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Max;

use function RunOpenCode\Component\Dataset\reduce;

final class MaxTest extends TestCase
{
    #[Test]
    public function max_value(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 3,
            'c' => null,
        ];

        $this->assertEquals(
            3,
            reduce($dataset, Max::class),
        );
    }

    #[Test]
    public function max_extracted_value(): void
    {
        $dataset = [3, 2, 1];

        $this->assertEquals(
            2,
            reduce($dataset, Max::class, extractor: static fn(int $value, int $key): int => $value * $key),
        );
    }
}
