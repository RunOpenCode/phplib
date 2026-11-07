<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\reduce;

final class ReduceTest extends TestCase
{
    #[Test]
    public function reduces_using_callback(): void
    {
        $dataset = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->assertSame(6, reduce(
            $dataset,
            static fn(int $carry, int $value): int => $carry + $value,
            initial: 0
        ));
    }

    #[Test]
    public function reduces_using_reducer(): void
    {
        $dataset = [
            'a' => 1,
            'b' => null,
            'c' => 3,
        ];

        $this->assertSame(4, reduce($dataset, Sum::class));
    }

    #[Test]
    public function returns_initial_when_source_empty(): void
    {
        $this->assertSame(10, reduce(
            [],
            static fn(int $carry, int $value): int => $carry + $value,
            initial: 10
        ));
    }
}
