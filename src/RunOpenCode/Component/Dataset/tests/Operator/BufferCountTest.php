<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Model\Buffer;
use RunOpenCode\Component\Dataset\Reducer\Count;

use function RunOpenCode\Component\Dataset\buffer_count;
use function RunOpenCode\Component\Dataset\stream;

final class BufferCountTest extends TestCase
{
    #[Test]
    public function buffers(): void
    {
        $stream = buffer_count([
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
            'e' => 7,
        ], 2)
            ->map(static function(Buffer $buffer): iterable {
                return stream($buffer)
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

        $this->assertSame(3, $stream->aggregators['count']->value);
    }

    #[Test]
    public function buffers_one_element(): void
    {
        $stream = buffer_count([
            'a' => 2,
        ], 2)
            ->map(static function(Buffer $buffer): iterable {
                return stream($buffer)
                    ->map(
                        static fn(int $value): int => $value * 2,
                        static fn(string $key): string => \sprintf('processed_%s', $key)
                    );
            })
            ->aggregate('count', Count::class)
            ->flatten(true);

        $this->assertSame([
            'processed_a' => 4,
        ], \iterator_to_array($stream));

        $this->assertSame(1, $stream->aggregators['count']->value);
    }

    #[Test]
    public function buffers_empty_stream(): void
    {
        $stream = buffer_count([], 10)->aggregate('count', Count::class);

        $this->assertSame([], \iterator_to_array($stream));

        $this->assertSame(0, $stream->aggregators['count']->value);
    }
}
