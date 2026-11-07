<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Dataset\Exception\RuntimeException;

use function RunOpenCode\Component\Dataset\zip_strict;

final class ZipStrictTest extends TestCase
{
    #[Test]
    public function equal_length_pairs(): void
    {
        $operator = zip_strict([
            'a' => 1,
            'b' => 2,
        ], [
            'c' => 3,
            'd' => 4,
        ]);

        $keys   = [];
        $values = [];

        foreach ($operator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }

        $this->assertSame([
            ['a', 'c'],
            ['b', 'd'],
        ], $keys);
        $this->assertSame([
            [1, 3],
            [2, 4],
        ], $values);
    }

    #[Test]
    public function sources_empty(): void
    {
        $operator = zip_strict([], []);

        // @phpstan-ignore-next-line method.impossibleType
        $this->assertSame([], \iterator_to_array($operator));
    }

    #[Test]
    public function first_source_longer_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);

        zip_strict([
            'a' => 1,
            'b' => 2,
            'f' => 5,
        ], [
            'c' => 3,
            'd' => 4,
        ])->flush();
    }

    #[Test]
    public function second_source_longer_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);

        zip_strict([
            'a' => 1,
            'b' => 2,
        ], [
            'c' => 3,
            'd' => 4,
            'f' => 5,
        ])->flush();
    }

    #[Test]
    public function first_source_empty_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);

        zip_strict([], [
            'c' => 3,
            'd' => 4,
        ])->flush();
    }

    #[Test]
    public function second_source_empty_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);

        zip_strict([
            'a' => 1,
            'b' => 2,
        ], [])->flush();
    }
}
