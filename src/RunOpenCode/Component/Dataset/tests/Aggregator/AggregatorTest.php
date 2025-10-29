<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Aggregator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Aggregator\Aggregator;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;

use function RunOpenCode\Component\Dataset\iterable_to_array;

final class AggregatorTest extends TestCase
{
    #[Test]
    public function iterates(): void
    {
        /** @var ReducerInterface<int, string, mixed>&MockObject $reducer */
        $reducer    = $this->createMock(ReducerInterface::class);
        $aggregator = new Aggregator('foo', $reducer);

        $reducer
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(['foo', 'bar', 'baz']));

        $this->assertSame([
            'foo',
            'bar',
            'baz',
        ], iterable_to_array($aggregator));
    }

    #[Test]
    public function provides_value(): void
    {
        /** @var ReducerInterface<int, string, int>&MockObject $reducer */
        $reducer    = $this->createMock(ReducerInterface::class);
        $aggregator = new Aggregator('foo', $reducer);

        $reducer
            ->expects($this->once())
            ->method(PropertyHook::get('value'))
            ->willReturn(42);

        $this->assertSame(42, $aggregator->value);
    }
}
