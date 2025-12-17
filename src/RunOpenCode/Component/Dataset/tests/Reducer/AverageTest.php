<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Reducer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Average;

use function RunOpenCode\Component\Dataset\reduce;

final class AverageTest extends TestCase
{
    #[Test]
    public function average_value(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ];

        $this->assertEqualsWithDelta(
            5.0,
            reduce($dataset, Average::class),
            0.0001
        );
    }

    #[Test]
    public function average_from_extracted_value(): void
    {
        $dataset = [
            'a' => [2],
            'b' => [4],
            'c' => [6],
            'd' => [null],
            'e' => [8],
        ];

        $this->assertEqualsWithDelta(
            5.0,
            reduce($dataset, Average::class, extractor: static fn(array $item): ?int => $item[0]), // @phpstan-ignore-line
            0.0001
        );
    }

    #[Test]
    public function average_with_initial_value(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ];

        $this->assertEqualsWithDelta(
            10.0,
            reduce($dataset, Average::class, initial: 20),
            0.0001
        );
    }

    #[Test]
    public function average_skips_nulls(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 4,
            'c' => 6,
            'd' => null,
            'e' => 8,
        ];

        $this->assertEqualsWithDelta(
            4.0,
            reduce($dataset, Average::class, countNull: true),
            0.0001
        );
    }
}
