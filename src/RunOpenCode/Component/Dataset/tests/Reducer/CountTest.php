<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Count;

use function RunOpenCode\Component\Dataset\reduce;

final class CountTest extends TestCase
{
    #[Test]
    public function counts_everything(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->assertSame(
            3,
            reduce($dataset, Count::class)
        );
    }

    #[Test]
    public function counts_filtered_only(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->assertSame(
            1,
            reduce($dataset, Count::class, filter: static fn(int $value, string $key): bool => $key !== 'b' && $value !== 1)
        );
    }
}
