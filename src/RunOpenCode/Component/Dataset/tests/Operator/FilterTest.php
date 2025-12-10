<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\filter;

final class FilterTest extends TestCase
{
    #[Test]
    public function filters(): void
    {
        $operator = filter([
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ], static fn(int $value, string $key): bool => $value > 2 && 'c' !== $key);

        $this->assertSame([
            'b' => 10,
        ], \iterator_to_array($operator));
    }
}
