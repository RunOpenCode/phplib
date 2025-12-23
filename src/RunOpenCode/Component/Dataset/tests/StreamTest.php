<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\ArrayCollector;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Model\Buffer;
use RunOpenCode\Component\Dataset\Reducer\Average;
use RunOpenCode\Component\Dataset\Reducer\Count;
use RunOpenCode\Component\Dataset\Reducer\Max;
use RunOpenCode\Component\Dataset\Reducer\Min;
use RunOpenCode\Component\Dataset\Reducer\Sum;
use RunOpenCode\Component\Dataset\Stream;

use function RunOpenCode\Component\Dataset\iterable_to_array;

final class StreamTest extends TestCase
{
    #[Test]
    public function creates(): void
    {
        $stream = Stream::create([1, 2, 3]);
        $this->assertSame([1, 2, 3], $stream->collect(ArrayCollector::class)->value);
    }

    #[Test]
    public function buffer_count(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
            'e' => 7,
        ];

        $stream = new Stream($dataset)
            ->bufferCount(2)
            ->map(static function(Buffer $buffer): iterable {
                return new Stream($buffer)
                    ->map(
                        static fn(int $value): int => $value * 2,
                        static fn(string $key): string => \sprintf('processed_%s', $key)
                    );
            })
            ->aggregate('count', Count::class)
            ->flatten(true);

        $this->assertSame([
            'processed_a' => 4,
            'processed_b' => 20,
            'processed_c' => 10,
            'processed_d' => 2,
            'processed_e' => 14,
        ], \iterator_to_array($stream));

        $this->assertSame(3, $stream->aggregated['count']);
    }

    #[Test]
    public function buffer_while(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 2,
            'c' => 2,
            'd' => 3,
            'e' => 3,
        ];

        $stream = new Stream($dataset)
            ->bufferWhile(static fn(Buffer $buffer, int $value): bool => $value === $buffer->last()->value()) // @phpstan-ignore-line
            ->aggregate('count', Count::class)
            ->map(static function(Buffer $buffer): iterable {
                return new Stream($buffer)
                    ->map(
                        static fn(int $value): int => $value * 2,
                        static fn(string $key): string => \sprintf('processed_%s', $key)
                    );
            })
            ->flatten(true);

        $this->assertSame([
            'processed_a' => 4,
            'processed_b' => 4,
            'processed_c' => 4,
            'processed_d' => 6,
            'processed_e' => 6,
        ], \iterator_to_array($stream));

        $this->assertSame(2, $stream->aggregated['count']);
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

    public function left_join(): void
    {
        $left  = [1 => 'a', 2 => 'b', 3 => 'c'];
        $right = [1 => 'x', 2 => 'y'];

        $joined = new Stream($left)
            ->leftJoin($right)
            ->collect(ArrayCollector::class)
            ->value;

        $this->assertSame([
            1 => ['a', ['x']],
            2 => ['b', ['y']],
            3 => ['c', []],
        ], $joined);
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
            ->tap(static function(int $value, string $key) use (&$tapped): void { // @phpstan-ignore-line
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

        $this->assertSame(12, $dataset->aggregated['middle_sum']);
        $this->assertSame(16, $dataset->aggregated['inner_sum']);
        $this->assertSame(28, $dataset->aggregated['total_sum']);
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

        $this->assertEqualsWithDelta(4.5, new Stream($dataset)->reduce(Average::class), PHP_FLOAT_EPSILON);
        $this->assertSame(4, new Stream($dataset)->reduce(Count::class));
        $this->assertSame(10, new Stream($dataset)->reduce(Max::class));
        $this->assertSame(1, new Stream($dataset)->reduce(Min::class));
        $this->assertSame(18, new Stream($dataset)->reduce(Sum::class));
        $this->assertSame(36, new Stream($dataset)->reduce(static fn(?int $carry, int $value, string $key): int => $value * 2 + ($carry ?? 0))); // @phpstan-ignore-line
    }

    #[Test]
    public function flush(): void
    {
        $dataset = [
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ];

        $stream = Stream::create($dataset)
                        ->aggregate('count', Count::class)
                        ->flush();

        $this->assertSame(4, $stream->aggregated['count']);
        $this->assertTrue($stream->closed);
    }

    #[Test]
    public function throws_exception_when_iterating_closed_stream(): void
    {
        $this->expectException(LogicException::class);

        $stream = new Stream([]);

        iterable_to_array($stream);
        \iterator_to_array($stream);
    }
}
