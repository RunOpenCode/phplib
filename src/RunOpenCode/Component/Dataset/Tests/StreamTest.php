<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\ArrayCollector;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Max;
use RunOpenCode\Component\Dataset\Reducer\Min;
use RunOpenCode\Component\Dataset\Reducer\Sum;
use RunOpenCode\Component\Dataset\Stream;

final class StreamTest extends TestCase
{
    #[Test]
    public function batch(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->batch(function(iterable $batch): iterable {
                foreach ($batch as [$key, $value]) {
                    yield \sprintf('processed_%s', $key) => $value * 2;
                }
            }, 2)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'processed_a' => 4,
            'processed_b' => 20,
            'processed_c' => 10,
            'processed_d' => 2,
        ], $data);
    }

    #[Test]
    public function distinct(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 2,
            'd' => 10,
        ];

        $data = new Stream($dataset)
            ->distinct()
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], $data);
    }

    #[Test]
    public function filter(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 2,
            'd' => 10,
        ];

        $data = new Stream($dataset)
            ->filter(static fn(int $value): bool => $value > 2)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'b' => 10,
            'd' => 10,
        ], $data);
    }

    #[Test]
    public function flatten(): void
    {
        $dataset = [
            'a' => [1, 2],
            'b' => [3, 4],
            'c' => [5],
        ];

        $data = new Stream($dataset)
            ->flatten()
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 4,
            4 => 5,
        ], $data);
    }

    #[Test]
    public function map(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 2,
            'd' => 10,
        ];

        $data = new Stream($dataset)
            ->map(
                static fn(int $value): int => $value * 2,
                static fn(string $key): string => \sprintf('processed_%s', $key),
            )
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'processed_a' => 4,
            'processed_b' => 20,
            'processed_c' => 4,
            'processed_d' => 20,
        ], $data);
    }

    #[Test]
    public function merge(): void
    {
        $dataset1 = [
            'a' => 2,
            'b' => 10,
        ];
        $dataset2 = [
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset1)
            ->merge($dataset2)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ], $data);
    }

    #[Test]
    public function reverse(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->reverse()
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'd' => 1,
            'c' => 5,
            'b' => 10,
            'a' => 2,
        ], $data);
    }

    #[Test]
    public function skip(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->skip(2)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'c' => 5,
            'd' => 1,
        ], $data);
    }

    #[Test]
    public function sort(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->sort()
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'd' => 1,
            'a' => 2,
            'c' => 5,
            'b' => 10,
        ], $data);
    }

    #[Test]
    public function take(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->take(2)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], $data);
    }

    #[Test]
    public function takeUntil(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $data = new Stream($dataset)
            ->takeUntil(static fn(int $value): bool => 5 === $value)
            ->collect(ArrayCollector::class)
            ->value;
        $this->assertSame([
            'a' => 2,
            'b' => 10,
        ], $data);
    }

    #[Test]
    public function tap(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $tapped = [];
        $data   = new Stream($dataset)
            ->tap(static function(int $value, string $key) use (&$tapped): void {
                $tapped[\sprintf('tapped_%s', $key)] = $value * 2;
            })
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame($dataset, $data);
        $this->assertSame([
            'tapped_a' => 4,
            'tapped_b' => 20,
            'tapped_c' => 10,
            'tapped_d' => 2,
        ], $tapped);
    }

    #[Test]
    public function aggregate(): void
    {
        $dataset1 = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
        ];

        $dataset2 = [
            'd' => 5,
            'e' => 1,
            'f' => 2,
        ];

        $dataset = new Stream($dataset1)
            ->take(2)
            ->aggregate('middle_sum', Sum::class)
            ->merge(
                new Stream($dataset2)
                    ->map(static fn(int $value): int => $value * 2)
                    ->aggregate('inner_sum', Sum::class)
            )
            ->aggregate('total_sum', Sum::class)
            ->collect(ArrayCollector::class);

        $this->assertSame([
            'a' => 2,
            'b' => 10,
            'd' => 10,
            'e' => 2,
            'f' => 4,
        ], $dataset->value);

        $this->assertSame(12, $dataset->aggregators['middle_sum']);
        $this->assertSame(16, $dataset->aggregators['inner_sum']);
        $this->assertSame(28, $dataset->aggregators['total_sum']);
    }

    #[Test]
    public function reduce(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $this->assertSame(4.5, new Stream($dataset)->reduce(Average::class));
        $this->assertSame(4, new Stream($dataset)->reduce(Count::class));
        $this->assertSame(10, new Stream($dataset)->reduce(Max::class));
        $this->assertSame(1, new Stream($dataset)->reduce(Min::class));
        $this->assertSame(18, new Stream($dataset)->reduce(Sum::class));
        $this->assertSame(36, new Stream($dataset)->reduce(static fn(?int $carry, int $value, string $key) => $value * 2 + ($carry ?? 0)));
    }
}
