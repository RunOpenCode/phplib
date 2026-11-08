<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\switch_map;

final class SwitchMapTest extends TestCase
{
    #[Test]
    public function switch_map(): void
    {
        $operator = switch_map([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], static function(int $value, string $key): iterable {
            for ($i = 0; $i < $value; $i++) {
                yield \sprintf('%s_%d', $key, $i) => $i;
            }
        });

        $this->assertSame([
            'a_0' => 0,
            'b_0' => 0,
            'b_1' => 1,
            'c_0' => 0,
            'c_1' => 1,
            'c_2' => 2,
        ], \iterator_to_array($operator));
    }
}