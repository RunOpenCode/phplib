<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Collector\ArrayCollector;
use RunOpenCode\Component\Dataset\Exception\LogicException;
use RunOpenCode\Component\Dataset\Operator;
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
        $this->assertInstanceOf(
            Operator\BufferCount::class,
            new Stream([])->bufferCount(2)->upstreams[0]
        );
    }

    #[Test]
    public function buffer_while(): void
    {
        $this->assertInstanceOf(
            Operator\BufferWhile::class,
            new Stream([])->bufferWhile(static fn(): bool => true)->upstreams[0]
        );
    }

    #[Test]
    public function distinct(): void
    {
        $this->assertInstanceOf(
            Operator\Distinct::class,
            new Stream([])->distinct()->upstreams[0]
        );
    }

    #[Test]
    public function filter(): void
    {
        $this->assertInstanceOf(
            Operator\Filter::class,
            new Stream([])->filter(static fn(): bool => true)->upstreams[0]
        );
    }

    #[Test]
    public function flatten(): void
    {
        $this->assertInstanceOf(
            Operator\Flatten::class,
            new Stream([])->flatten()->upstreams[0]
        );
    }

    #[Test]
    public function flip(): void
    {
        $this->assertInstanceOf(
            Operator\Flip::class,
            new Stream([])->flip()->upstreams[0]
        );
    }

    #[Test]
    public function if_empty(): void
    {
        $this->assertInstanceOf(
            Operator\IfEmpty::class,
            new Stream([])->ifEmpty()->upstreams[0]
        );
    }

    #[Test]
    public function index_by(): void
    {
        $this->assertInstanceOf(
            Operator\IndexBy::class,
            new Stream([])->indexBy(static fn(): null => null)->upstreams[0]
        );
    }

    #[Test]
    public function keys(): void
    {
        $this->assertInstanceOf(
            Operator\Keys::class,
            new Stream([])->keys()->upstreams[0]
        );
    }

    #[Test]
    public function left_join(): void
    {
        $this->assertInstanceOf(
            Operator\LeftJoin::class,
            new Stream([])->leftJoin([])->upstreams[0]
        );
    }

    #[Test]
    public function map(): void
    {
        $this->assertInstanceOf(
            Operator\Map::class,
            new Stream([])->map(static fn() => null)->upstreams[0]
        );
    }

    #[Test]
    public function merge(): void
    {
        $this->assertInstanceOf(
            Operator\Merge::class,
            new Stream([])->merge([])->upstreams[0]
        );
    }

    #[Test]
    public function overflow(): void
    {
        $this->assertInstanceOf(
            Operator\Overflow::class,
            new Stream([])->overflow(1)->upstreams[0]
        );
    }

    #[Test]
    public function pairwise(): void
    {
        $this->assertInstanceOf(
            Operator\Pairwise::class,
            new Stream([])->pairwise()->upstreams[0]
        );
    }

    #[Test]
    public function reduce(): void
    {
        $this->assertSame(
            0,
            new Stream([])->reduce(Count::class)
        );
    }

    #[Test]
    public function reverse(): void
    {
        $this->assertInstanceOf(
            Operator\Reverse::class,
            new Stream([])->reverse()->upstreams[0]
        );
    }

    #[Test]
    public function skip(): void
    {
        $this->assertInstanceOf(
            Operator\Skip::class,
            new Stream([])->skip(1)->upstreams[0]
        );
    }

    #[Test]
    public function sort(): void
    {
        $this->assertInstanceOf(
            Operator\Sort::class,
            new Stream([])->sort()->upstreams[0]
        );
    }

    #[Test]
    public function take(): void
    {
        $this->assertInstanceOf(
            Operator\Take::class,
            new Stream([])->take(1)->upstreams[0]
        );
    }

    #[Test]
    public function take_until(): void
    {
        $this->assertInstanceOf(
            Operator\TakeUntil::class,
            new Stream([])->takeUntil(static fn(): bool => true)->upstreams[0]
        );
    }

    #[Test]
    public function tap(): void
    {
        $this->assertInstanceOf(
            Operator\Tap::class,
            new Stream([])->tap(static fn(): null => null)->upstreams[0]
        );
    }

    #[Test]
    public function values(): void
    {
        $this->assertInstanceOf(
            Operator\Values::class,
            new Stream([])->values()->upstreams[0]
        );
    }

    #[Test]
    public function with_all(): void
    {
        $this->assertInstanceOf(
            Operator\WithAll::class,
            new Stream([])->withAll(static fn(): null => null)->upstreams[0]
        );
    }

    #[Test]
    public function zip(): void
    {
        $this->assertInstanceOf(
            Operator\ZipStrict::class,
            new Stream([])->zip([])->upstreams[0]
        );
    }

    #[Test]
    public function zipLongest(): void
    {
        $this->assertInstanceOf(
            Operator\ZipLongest::class,
            new Stream([])->zipLongest([])->upstreams[0]
        );
    }

    #[Test]
    public function zipShortest(): void
    {
        $this->assertInstanceOf(
            Operator\ZipShortest::class,
            new Stream([])->zipShortest([])->upstreams[0]
        );
    }

    #[Test]
    public function zipStrict(): void
    {
        $this->assertInstanceOf(
            Operator\ZipStrict::class,
            new Stream([])->zipStrict([])->upstreams[0]
        );
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
