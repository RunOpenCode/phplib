<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\iterable_to_array;
use function RunOpenCode\Component\Dataset\tap;

final class TapTest extends TestCase
{
    #[Test]
    public function taps(): void
    {
        $log      = [];
        $operator = tap([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], static function(int $value, string $key) use (&$log): void {
            $log[] = \sprintf('Key: %s, Value: %d', $key, $value);
        });

        iterable_to_array($operator);

        $this->assertSame([
            'Key: a, Value: 1',
            'Key: b, Value: 2',
            'Key: c, Value: 3',
        ], $log);
    }
}
