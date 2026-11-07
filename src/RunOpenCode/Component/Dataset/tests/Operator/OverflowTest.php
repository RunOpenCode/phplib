<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\StreamOverflowException;

use function RunOpenCode\Component\Dataset\overflow;

final class OverflowTest extends TestCase
{
    #[Test]
    public function capacity_equals(): void
    {
        $operator = overflow([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], 3);

        $this->assertSame([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], \iterator_to_array($operator));
    }

    #[Test]
    public function empty_source(): void
    {
        $operator = overflow([], 1);

        $this->assertSame([], \iterator_to_array($operator));
    }

    #[Test]
    public function exceeded_capacity_throws_exception(): void
    {
        $this->expectException(StreamOverflowException::class);

        overflow([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], 2)->flush();
    }

    #[Test]
    public function exceeded_capacity_throws_custom_throwable(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('foo');

        overflow([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], 2, new \Exception('foo'))->flush();
    }

    #[Test]
    public function exceeded_capacity_throws_custom_throwable_from_factory(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('foo');

        overflow([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ], 2, static fn(): \Exception => new \Exception('foo'))->flush();
    }
}
