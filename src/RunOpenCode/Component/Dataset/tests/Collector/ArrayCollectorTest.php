<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Collector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\ArrayCollector;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\collect;
use function RunOpenCode\Component\Dataset\stream;

final class ArrayCollectorTest extends TestCase
{
    #[Test]
    public function iterates(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = collect($dataset, ArrayCollector::class);

        $this->assertSame($dataset, \iterator_to_array($collector));
        $this->assertSame($dataset, $collector->value);
    }

    #[Test]
    public function array_access(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = collect($dataset, ArrayCollector::class);

        $this->assertSame(2, $collector['a']);
        $this->assertArrayHasKey('b', $collector);
    }

    #[Test]
    public function counts(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = collect($dataset, ArrayCollector::class);

        $this->assertCount(4, $collector);
    }

    #[Test]
    public function aggregates(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
        ];

        $collector = stream($dataset)
            ->aggregate('count', Count::class)
            ->aggregate('sum', Sum::class)
            ->aggregate('average', Average::class)
            ->collect(ArrayCollector::class);

        $this->assertSame(2, $collector->aggregated['count']);
        $this->assertSame(12, $collector->aggregated['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregated['average'], 0.0001);
    }

    #[Test]
    public function array_access_set_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        collect([], ArrayCollector::class)['foo'] = 'bar';
    }

    #[Test]
    public function array_access_unset_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        unset(collect([], ArrayCollector::class)['foo']);
    }
}
