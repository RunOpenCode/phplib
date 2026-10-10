<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Collector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\RewindableCollector;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Sum;

use function RunOpenCode\Component\Dataset\collect;
use function RunOpenCode\Component\Dataset\stream;

final class RewindableCollectorTest extends TestCase
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

        $collector = collect($dataset, RewindableCollector::class);

        $this->assertSame($dataset, \iterator_to_array($collector));
        $this->assertSame(4, $collector->count);

        // Rewind and iterate again
        $this->assertSame($dataset, \iterator_to_array($collector));
        $this->assertSame(4, $collector->count);
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
            ->collect(RewindableCollector::class);

        $this->assertSame(2, $collector->aggregated['count']);
        $this->assertSame(12, $collector->aggregated['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregated['average'], 0.0001);

        // Rewind and aggregate again
        $this->assertSame(2, $collector->aggregated['count']);
        $this->assertSame(12, $collector->aggregated['sum']);
        $this->assertEqualsWithDelta(6, $collector->aggregated['average'], 0.0001);
    }
}
