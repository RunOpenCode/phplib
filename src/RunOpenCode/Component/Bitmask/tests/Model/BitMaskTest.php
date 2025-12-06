<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Bitmask\Exception\InvalidArgumentException;
use RunOpenCode\Component\Bitmask\Exception\OutOfBoundsException;
use RunOpenCode\Component\Bitmask\Model\Bitmask;

final class BitmaskTest extends TestCase
{
    #[Test]
    #[TestWith([8, '00000000'])]
    #[TestWith([16, '0000000000000000'])]
    #[TestWith([32, '00000000000000000000000000000000'])]
    public function zeroes(int $length, string $expected): void
    {
        $this->assertSame($expected, Bitmask::zeroes($length)->toString());
    }

    #[Test]
    #[TestWith([10])]
    #[TestWith([12])]
    public function zeroes_throw_exception_when_length_is_not_dividable_by_8(int $length): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::zeroes($length);
    }

    #[Test]
    #[TestWith(['01001001', 8])]
    #[TestWith(['0000011000110000', 16])]
    #[TestWith(['00000001100000000000001000010010', 32])]
    public function string(string $input, int $length): void
    {
        $bitmask = Bitmask::string($input);

        $this->assertSame($input, $bitmask->toString());
        $this->assertCount($length, $bitmask);
    }

    #[Test]
    #[TestWith(['010011'])]
    #[TestWith(['01001001001001'])]
    public function string_throw_exception_when_length_is_not_dividable_by_8(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string($input);
    }

    #[Test]
    public function string_throw_exception_on_invalid_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string('foo');
    }

    #[Test]
    #[TestWith(['AA==', '00000000'])]
    #[TestWith(['IA==', '00000100'])]
    public function binary(string $encoded, string $expected): void
    {
        $bitmask = Bitmask::binary(\Safe\base64_decode($encoded, true));

        $this->assertSame($expected, $bitmask->toString());
        $this->assertSame(8, \strlen($expected));
    }

    #[Test]
    public function empty(): void
    {
        $empty = Bitmask::string('00000000');
        $nonEmpty = Bitmask::string('00100000');

        $this->assertTrue($empty->empty());
        $this->assertFalse($nonEmpty->empty());
    }

    #[Test]
    #[TestWith(['01001001', 3])]
    #[TestWith(['0000011000110000', 4])]
    #[TestWith(['00000001100000000000001000010010', 5])]
    public function cardinality(string $input, int $expected): void
    {
        $this->assertSame($expected, Bitmask::string($input)->cardinality());
    }

    #[Test]
    public function true(): void
    {
        $bitmask = Bitmask::string('00000000');

        $this->assertFalse($bitmask->get(7));

        $bitmask = $bitmask->true(7);

        $this->assertTrue($bitmask->get(7));
        $this->assertSame('00000001', $bitmask->toString());
    }

    #[Test]
    public function true_throws_exception_on_invalid_offset(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Bitmask::string('00000000')->true(10);
    }

    #[Test]
    public function false(): void
    {
        $bitmask = Bitmask::string('00000001');

        $this->assertTrue($bitmask->get(7));

        $bitmask = $bitmask->false(7);

        $this->assertFalse($bitmask->get(7));
        $this->assertSame('00000000', $bitmask->toString());
    }

    #[Test]
    public function false_throws_exception_on_invalid_offset(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Bitmask::string('00000000')->true(10);
    }

    #[Test]
    public function set(): void
    {
        $bitmask = Bitmask::string('00000001');

        $this->assertTrue($bitmask->get(7));

        $bitmask = $bitmask->set(7, false);

        $this->assertFalse($bitmask->get(7));
        $this->assertSame('00000000', $bitmask->toString());

        $bitmask = $bitmask->set(7, true);

        $this->assertTrue($bitmask->get(7));
        $this->assertSame('00000001', $bitmask->toString());
    }

    #[Test]
    public function set_throws_exception_on_invalid_offset(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Bitmask::string('00000000')->set(10, true);
    }

    #[Test]
    #[TestWith(['01001001', '00001000', '00001000'])]
    #[TestWith(['11111111', '00000000', '00000000'])]
    #[TestWith(['11111111', '11111111', '11111111'])]
    public function and(string $first, string $second, string $expected): void
    {
        $this->assertSame($expected, Bitmask::string($first)->and(Bitmask::string($second))->toString());
    }

    #[Test]
    public function and_throws_exception_on_invalid_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string('00000000')->and(Bitmask::string('0000000000000000'));
    }

    #[Test]
    #[TestWith(['01001001', '00001000', '01000001'])]
    #[TestWith(['11111111', '00000000', '11111111'])]
    #[TestWith(['11111111', '11111111', '00000000'])]
    public function andNot(string $first, string $second, string $expected): void
    {
        $this->assertSame($expected, Bitmask::string($first)->andNot(Bitmask::string($second))->toString());
    }

    #[Test]
    public function and_not_throws_exception_on_invalid_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string('00000000')->andNot(Bitmask::string('0000000000000000'));
    }

    #[Test]
    #[TestWith(['01001001', '00001000', '01001001'])]
    #[TestWith(['11111111', '00000000', '11111111'])]
    #[TestWith(['11110000', '00001111', '11111111'])]
    public function or(string $first, string $second, string $expected): void
    {
        $this->assertSame($expected, Bitmask::string($first)->or(Bitmask::string($second))->toString());
    }

    #[Test]
    public function or_throws_exception_on_invalid_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string('00000000')->or(Bitmask::string('0000000000000000'));
    }

    #[Test]
    #[TestWith(['01001001', '00001000', '01000001'])]
    #[TestWith(['11111111', '00000000', '11111111'])]
    #[TestWith(['11110000', '00001111', '11111111'])]
    public function xor(string $first, string $second, string $expected): void
    {
        $this->assertSame($expected, Bitmask::string($first)->xor(Bitmask::string($second))->toString());
    }

    #[Test]
    public function xor_throws_exception_on_invalid_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bitmask::string('00000000')->xor(Bitmask::string('0000000000000000'));
    }

    #[Test]
    #[TestWith(['00000000', 'AA=='])]
    #[TestWith(['00000100', 'IA=='])]
    public function to_binary(string $input, string $expected): void
    {
        $this->assertSame($expected, \base64_encode(Bitmask::string($input)->toBinary()));
    }

    #[Test]
    public function iterates(): void
    {
        $this->assertSame([
            0 => true,
            1 => false,
            2 => false,
            3 => true,
            4 => false,
            5 => false,
            6 => true,
            7 => true,
        ], \iterator_to_array(Bitmask::string('10010011')));
    }
}
