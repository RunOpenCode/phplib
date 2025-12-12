<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Model\Buffer;
use RunOpenCode\Component\Dataset\Reducer\Count;

use function RunOpenCode\Component\Dataset\buffer_while;
use function RunOpenCode\Component\Dataset\stream;

final class BufferWhileTest extends TestCase
{
    #[Test]
    public function buffers(): void
    {
        $stream = buffer_while(
            [
                'a' => 2,
                'b' => 2,
                'c' => 2,
                'd' => 3,
                'e' => 3,
            ],
            static fn(Buffer $buffer, int $value): bool => $value === $buffer->last()->value(), // @phpstan-ignore-line
        )
            ->aggregate('count', Count::class)
            ->map(static function(Buffer $buffer): iterable {
                return stream($buffer)
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

        $this->assertSame(2, $stream->aggregators['count']->value);
    }

    #[Test]
    public function buffers_one_element(): void
    {
        $stream = buffer_while(
            [
                'a' => 2,
            ],
            static fn(Buffer $buffer, int $value): bool => $value === $buffer->last()->key(), // @phpstan-ignore-line
        )
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
        $stream = buffer_while(
            [],
            static fn(): never => throw new \Exception('Never to be called'),
        )->aggregate('count', Count::class);

        $this->assertSame([], \iterator_to_array($stream));

        $this->assertSame(0, $stream->aggregators['count']->value);
    }
}
