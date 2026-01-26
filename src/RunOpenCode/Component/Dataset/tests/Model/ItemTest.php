<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Model\Item;

final class ItemTest extends TestCase
{
    #[Test]
    public function key(): void
    {
        $this->assertSame('1', new Item('1', 1)->key());
    }

    #[Test]
    public function value(): void
    {
        $this->assertSame(1, new Item('1', 1)->value());
    }

    #[Test]
    public function array_access(): void
    {
        $item = new Item('1', 1);

        $this->assertArrayHasKey(0, $item); // @phpstan-ignore-line argument.type
        $this->assertArrayHasKey(1, $item); // @phpstan-ignore-line argument.type
        $this->assertSame('1', $item[0]);
        $this->assertSame(1, $item[1]);
    }

    #[Test]
    public function offset_set_throws_exception(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $item = new Item('1', 1);

        $item[0] = 'foo';
    }

    #[Test]
    public function offset_unset_throws_exception(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $item = new Item('1', 1);

        unset($item[0]);
    }
}
