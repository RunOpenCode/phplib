<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Collector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\IndexedCollector;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\collect;
use function RunOpenCode\Component\Dataset\stream;

final class IndexCollectorTest extends TestCase
{
    #[Test]
    public function iterates(): void
    {
        $generator = static function(): iterable {
            yield 'a' => 1;
            yield 'a' => 2;
            yield 'b' => 3;
        };

        $collector = collect($generator(), IndexedCollector::class);
        $collected = [];

        foreach ($collector as $key => $value) {
            $collected[] = [$key, $value];
        }
        $this->assertSame([
            ['a', 1],
            ['a', 2],
            ['b', 3],
        ], $collected);
    }

    #[Test]
    public function rewindable(): void
    {
        $generator = static function(): iterable {
            yield 'a' => 1;
            yield 'a' => 2;
            yield 'b' => 3;
        };

        $collector = collect($generator(), IndexedCollector::class);

        \iterator_to_array($collector);
        \iterator_to_array($collector);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function array_access_scalar_keys(): void
    {
        $generator = static function(): iterable {
            yield 0 => 'a';
            yield 0 => 'b';
            yield 1 => 'c';
        };

        $collector = collect($generator(), IndexedCollector::class);

        $this->assertSame(['a', 'b'], $collector[0]);
        $this->assertSame(['c'], $collector[1]);
    }

    #[Test]
    public function array_access_object_keys(): void
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $generator = static function() use ($object1, $object2) {
            yield $object1 => 'a';
            yield $object1 => 'b';
            yield $object2 => 'c';
        };

        $collector = collect($generator(), IndexedCollector::class);

        $this->assertSame(['a', 'b'], $collector[$object1]);
        $this->assertSame(['c'], $collector[$object2]);
    }

    #[Test]
    public function counts(): void
    {
        $dataset = [2, 10, 5, 1];

        $collector = collect($dataset, IndexedCollector::class);

        $this->assertCount(4, $collector);
    }

    #[Test]
    public function aggregates(): void
    {
        $dataset = [2, 10];

        $collector = stream($dataset)
            ->aggregate('count', Count::class)
            ->aggregate('sum', Sum::class)
            ->aggregate('average', Average::class)
            ->collect(IndexedCollector::class);

        $this->assertSame(2, $collector->aggregated['count']);
        $this->assertSame(12, $collector->aggregated['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregated['average'], 0.0001);
    }

    #[Test]
    public function array_access_set_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        collect([], IndexedCollector::class)[10] = ['bar'];
    }

    #[Test]
    public function array_access_unset_throws_exception(): void
    {
        $this->expectException(LogicException::class);

        unset(collect([], IndexedCollector::class)[20]);
    }
}
