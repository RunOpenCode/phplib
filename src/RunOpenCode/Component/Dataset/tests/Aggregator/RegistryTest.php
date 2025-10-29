<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\Dataset\Tests\Aggregator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Aggregator\Registry;
use RunOpenCode\Component\Dataset\Contract\AggregatorInterface;
use RunOpenCode\Component\Dataset\Exception\LogicException;

final class RegistryTest extends TestCase
{
    #[Test]
    #[TestDox('Registers, counts and iterates')]
    public function registers_counts_and_iterates(): void
    {
        $registry = new Registry();
        $first    = $this->createMock(AggregatorInterface::class);
        $second   = $this->createMock(AggregatorInterface::class);

        $first
            ->method(PropertyHook::get('name'))
            ->willReturn('first');

        $second
            ->method(PropertyHook::get('name'))
            ->willReturn('second');

        $registry->register($first);
        $registry->register($second);

        $this->assertCount(2, $registry);
        $this->assertSame($first, $registry['first']);
        $this->assertSame($second, $registry['second']);
        $this->assertSame([
            'first'  => $first,
            'second' => $second,
        ], \iterator_to_array($registry));
    }

    #[Test]
    public function ignores_duplicates(): void
    {
        $registry   = new Registry();
        $aggregator = $this->createMock(AggregatorInterface::class);

        $aggregator
            ->method(PropertyHook::get('name'))
            ->willReturn('foo');

        $registry->register($aggregator);
        $registry->register($aggregator);
        $registry['foo'] = $aggregator; // @phpstan-ignore-line offsetAssign.valueType
        $registry['foo'] = $aggregator; // @phpstan-ignore-line offsetAssign.valueType

        $this->assertCount(1, $registry);
        $this->assertSame($aggregator, $registry['foo']);
    }

    #[Test]
    public function array_access(): void
    {
        $registry   = new Registry();
        $aggregator = $this->createMock(AggregatorInterface::class);

        $aggregator
            ->method(PropertyHook::get('name'))
            ->willReturn('foo');

        $registry['foo'] = $aggregator; // @phpstan-ignore-line offsetAssign.valueType

        $this->assertCount(1, $registry);
        $this->assertSame($aggregator, $registry['foo']);
        // @phpstan-ignore-next-line isset.offset
        $this->assertArrayHasKey('foo', $registry);

        unset($registry['foo']);

        $this->assertCount(0, $registry);
        $this->assertArrayNotHasKey('foo', $registry); // @phpstan-ignore-line argument.type
    }

    #[Test]
    public function throws_exception_on_name_collisions(): void
    {
        $this->expectException(LogicException::class);

        $registry = new Registry();
        $first    = $this->createMock(AggregatorInterface::class);
        $second   = $this->createMock(AggregatorInterface::class);

        $first
            ->method(PropertyHook::get('name'))
            ->willReturn('first');

        $second
            ->method(PropertyHook::get('name'))
            ->willReturn('first');

        $registry->register($first);
        $registry->register($second);
    }

    #[Test]
    public function throws_exception_on_name_mismatch(): void
    {
        $this->expectException(LogicException::class);

        $registry   = new Registry();
        $aggregator = $this->createMock(AggregatorInterface::class);

        $aggregator
            ->method(PropertyHook::get('name'))
            ->willReturn('foo');

        $registry['bar'] = $aggregator; // @phpstan-ignore-line offsetAssign.valueType
    }
}
