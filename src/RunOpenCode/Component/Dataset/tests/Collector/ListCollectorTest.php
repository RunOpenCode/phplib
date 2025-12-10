<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Collector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\ListCollector;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\collect;
use function RunOpenCode\Component\Dataset\stream;

final class ListCollectorTest extends TestCase
{
    #[Test]
    public function iterates(): void
    {
        $dataset = [2, 10, 5, 1];

        $collector = collect($dataset, ListCollector::class);

        $this->assertSame($dataset, \iterator_to_array($collector));
        $this->assertSame($dataset, $collector->value);
    }

    #[Test]
    public function array_access(): void
    {
        $dataset = [2, 10, 5, 1];

        $collector = collect($dataset, ListCollector::class);

        $this->assertSame(2, $collector[0]);
        $this->assertArrayHasKey(1, $collector);
    }

    #[Test]
    public function counts(): void
    {
        $dataset = [2, 10, 5, 1];

        $collector = collect($dataset, ListCollector::class);

        $this->assertCount(4, $collector);
    }

    #[Test]
    public function converts_to_list(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = collect($dataset, ListCollector::class);

        $this->assertSame(\array_values($dataset), \iterator_to_array($collector));
        $this->assertSame(\array_values($dataset), $collector->value);
    }

    #[Test]
    public function aggregates(): void
    {
        $dataset = [2, 10];

        $collector = stream($dataset)
            ->aggregate('count', Count::class)
            ->aggregate('sum', Sum::class)
            ->aggregate('average', Average::class)
            ->collect(ListCollector::class);

        $this->assertSame(2, $collector->aggregators['count']);
        $this->assertSame(12, $collector->aggregators['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregators['average'], 0.0001);
    }

    #[Test]
    public function array_access_set_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        collect([], ListCollector::class)[10] = 'bar';
    }

    #[Test]
    public function array_access_unset_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        unset(collect([], ListCollector::class)[20]);
    }
}
