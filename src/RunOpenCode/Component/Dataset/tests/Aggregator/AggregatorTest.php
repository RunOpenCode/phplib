<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Aggregator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Aggregator\Aggregator;
use RunOpenCode\Component\Dataset\Contract\ReducerInterface;
use RunOpenCode\Component\Dataset\Operator\Reduce;
use RunOpenCode\Component\Dataset\Reducer\Callback;

use function RunOpenCode\Component\Dataset\iterable_to_array;

final class AggregatorTest extends TestCase
{
    #[Test]
    public function iterates(): void
    {
        /** @var ReducerInterface<int, string, mixed>&MockObject $reducer */
        $reducer    = $this->createMock(ReducerInterface::class);
        $aggregator = new Aggregator('foo', new Reduce([
            'foo',
            'bar',
            'baz',
        ], $reducer));

        $reducer
            ->expects($this->exactly(3))
            ->method('next');

        $this->assertSame([
            'foo',
            'bar',
            'baz',
        ], iterable_to_array($aggregator));
    }

    #[Test]
    public function provides_value(): void
    {
        $reducer    = new Callback(static fn(string $carry, string $value): string => \sprintf('%s/%s', $carry, $value), '');
        $aggregator = new Aggregator('foo', new Reduce([
            'foo',
            'bar',
            'baz',
        ], $reducer));

        iterable_to_array($aggregator); // @phpstan-ignore-line

        $this->assertSame('/foo/bar/baz', $aggregator->value);
    }
}
