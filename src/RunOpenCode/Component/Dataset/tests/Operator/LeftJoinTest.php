<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\left_join;

final class LeftJoinTest extends TestCase
{
    #[Test]
    public function joins(): void
    {
        $left   = [1 => 'a', 2 => 'b', 3 => 'c'];
        $right  = [1 => 'x', 2 => 'y'];
        $joined = left_join($left, $right);

        $this->assertSame([
            1 => ['a', ['x']],
            2 => ['b', ['y']],
            3 => ['c', []],
        ], \iterator_to_array($joined));
    }

    #[Test]
    public function left_joins_with_duplicate_right_keys(): void
    {
        $left   = [1 => 'a', 2 => 'b'];
        $right  = (static function(): iterable {
            yield 1 => 'x';
            yield 1 => 'y';
            yield 1 => 'z';
        })();
        $joined = left_join($left, $right);

        $this->assertSame([
            1 => ['a', ['x', 'y', 'z']],
            2 => ['b', []],
        ], \iterator_to_array($joined));
    }
}
