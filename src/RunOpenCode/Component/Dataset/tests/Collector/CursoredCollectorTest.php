<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\tests\Collector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\CursoredCollector;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\collect;
use function RunOpenCode\Component\Dataset\stream;

final class CursoredCollectorTest extends TestCase
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

        $collector = collect($dataset, CursoredCollector::class);

        $this->assertSame($dataset, \iterator_to_array($collector));
        $this->assertNull($collector->previous);
        $this->assertNull($collector->next);
    }

    #[Test]
    public function offsets(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = collect($dataset, CursoredCollector::class, offset: 2, limit: 2);

        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], \iterator_to_array($collector));
        $this->assertSame(0, $collector->previous);
        $this->assertSame(4, $collector->next);
    }

    #[Test]
    public function aggregates(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $collector = stream($dataset)
            ->aggregate('count', Count::class)
            ->aggregate('sum', Sum::class)
            ->aggregate('average', Average::class)
            ->collect(CursoredCollector::class, offset: 6, limit: 2);

        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], \iterator_to_array($collector));

        $this->assertSame(4, $collector->previous);
        $this->assertSame(8, $collector->next);
        $this->assertSame(2, $collector->aggregators['count']);
        $this->assertSame(12, $collector->aggregators['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregators['average'], 0.0001);
    }
}
