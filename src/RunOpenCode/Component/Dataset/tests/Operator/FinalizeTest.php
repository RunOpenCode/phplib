<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\finalize;

final class FinalizeTest extends TestCase
{
    #[Test]
    public function runs_after_iteration_complete(): void
    {
        $finalizedCount = 0;

        $operator = finalize([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], static function() use (&$finalizedCount): void {
            ++$finalizedCount;
        });

        $this->assertEquals([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], \iterator_to_array($operator));

        $this->assertSame(1, $finalizedCount);
    }

    #[Test]
    public function runs_when_iteration_is_interrupted(): void
    {
        $finalizedCount = 0;

        $operator = finalize([
            10 => 'a',
            15 => 'b',
            5  => 'c',
        ], static function() use (&$finalizedCount): void {
            ++$finalizedCount;
        });

        foreach ($operator as $key => $value) {
            $this->assertEquals(10, $key);
            $this->assertEquals('a', $value);

            break;
        }

        unset($operator);

        $this->assertSame(1, $finalizedCount);
    }

    #[Test]
    public function runs_when_source_throws_exception(): void
    {
        $finalizedCount = 0;

        $operator = finalize((static function(): \Generator {
            yield 'a' => 1;
            throw new \RuntimeException();
        })(), static function() use (&$finalizedCount): void {
            ++$finalizedCount;
        });

        try {
            $operator->flush();
            $this->fail('Expected exception to be thrown.');
        } catch (\RuntimeException) {
            // noop
        }

        $this->assertSame(1, $finalizedCount);
    }

    #[Test]
    public function runs_without_iteration(): void
    {
        $finalizedCount = 0;

        $operator = finalize([
            10 => 'a',
        ], static function() use (&$finalizedCount): void {
            ++$finalizedCount;
        });

        unset($operator);

        $this->assertSame(1, $finalizedCount);
    }
}
