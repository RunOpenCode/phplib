<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Model\Buffer;

final class BufferTest extends TestCase
{
    #[Test]
    public function first(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame('a', $buffer->first()->key());
        $this->assertSame(1, $buffer->first()->value());
    }

    #[Test]
    public function first_throws_exception_on_empty_buffer(): void
    {
        $this->expectException(LogicException::class);

        new Buffer(new \ArrayObject())->first();
    }

    #[Test]
    public function last(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame('b', $buffer->last()->key());
        $this->assertSame(2, $buffer->last()->value());
    }

    #[Test]
    public function last_throws_exception_on_empty_buffer(): void
    {
        $this->expectException(LogicException::class);

        new Buffer(new \ArrayObject())->last();
    }

    #[Test]
    public function keys(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame(['a', 'b'], $buffer->keys());
    }

    #[Test]
    public function values(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame([1, 2], $buffer->values());
    }

    #[Test]
    public function counts(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertCount(2, $buffer);
    }

    #[Test]
    public function iterates(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame([
            'a' => 1,
            'b' => 2,
        ], \iterator_to_array($buffer));
    }

    #[Test]
    public function streams(): void
    {
        $buffer = new Buffer(new \ArrayObject([
            ['a', 1],
            ['b', 2],
        ]));

        $this->assertSame([
            'a' => 1,
            'b' => 2,
        ], \iterator_to_array($buffer->stream()));
    }
}
