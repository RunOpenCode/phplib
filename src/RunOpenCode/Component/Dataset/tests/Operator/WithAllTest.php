<?php

declare(strict_types=1);

namespace Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Model\Buffer;

use function RunOpenCode\Component\Dataset\withAll;

final class WithAllTest extends TestCase
{
    #[Test]
    public function withAll(): void
    {
        $collected = [];
        $finalized = [];

        $operator = withAll([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], \Closure::bind(function(Buffer $buffer) use (&$collected, &$finalized): void {
            // @phpstan-ignore-next-line method.impossibleType
            $this->assertNotEmpty($collected);
            $this->assertEmpty($finalized);

            $finalized = \iterator_to_array($buffer);

            $this->assertSame([
                10 => 'a',
                15 => 'b',
                5  => 'c',
            ], $finalized);
        }, $this));

        foreach ($operator as $key => $value) {
            $collected[$key] = $value;
        }

        $this->assertSame([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], $collected);

        $this->assertSame($collected, $finalized);
    }

    #[Test]
    public function interrupted(): void
    {
        $collected = [];
        $finalized = [];

        $operator = withAll([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], \Closure::bind(function(Buffer $buffer) use (&$collected, &$finalized): void {
            // @phpstan-ignore-next-line method.impossibleType
            $this->assertNotEmpty($collected);
            $this->assertEmpty($finalized);

            $finalized = \iterator_to_array($buffer);

            $this->assertSame([
                10 => 'a',
            ], $finalized);
        }, $this));

        foreach ($operator as $key => $value) {
            $collected[$key] = $value;
            break;
        }

        unset($operator);

        $this->assertSame([
            10 => 'a',
        ], $collected);

        $this->assertSame($collected, $finalized);
    }
}
