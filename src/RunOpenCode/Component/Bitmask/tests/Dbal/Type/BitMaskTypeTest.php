<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Tests\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Bitmask\Dbal\Type\BitmaskType;
use RunOpenCode\Component\Bitmask\Model\Bitmask;

final class BitmaskTypeTest extends TestCase
{
    private BitmaskType $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new BitmaskType();
    }

    #[Test]
    public function get_name(): void
    {
        $this->assertSame('bitmask', $this->type->getName());
    }

    #[Test]
    #[DataProvider('data_provider')]
    public function convert_to_database_value(Bitmask $mask, string $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($mask, $this->createMock(AbstractPlatform::class)));
    }

    #[Test]
    #[DataProvider('data_provider')]
    public function convert_string_to_php_value(Bitmask $expected, string $mask): void
    {
        $this->assertTrue($expected->equals(
            $this->type->convertToPHPValue($mask, $this->createMock(AbstractPlatform::class))
        ));
    }

    #[Test]
    #[DataProvider('data_provider')]
    public function convert_stream_to_php_value(Bitmask $expected, string $mask): void
    {
        $stream = \Safe\fopen('php://memory', 'rwb');

        \Safe\fwrite($stream, $mask);
        \Safe\rewind($stream);

        $this->assertTrue($expected->equals(
            $this->type->convertToPHPValue($stream, $this->createMock(AbstractPlatform::class))
        ));

        \fclose($stream);
    }

    #[Test]
    public function convert_null_to_null(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $this->assertNull($this->type->convertToPHPValue(null, $platform)); // @phpstan-ignore-line
        $this->assertNull($this->type->convertToDatabaseValue(null, $platform)); // @phpstan-ignore-line
    }

    /**
     * @return iterable<string, array{Bitmask, string}>
     */
    public static function data_provider(): iterable
    {
        yield '00000000' => [Bitmask::string('00000000'), \Safe\base64_decode('AA==', true)];
        yield '00000100' => [Bitmask::string('00000100'), \Safe\base64_decode('IA==', true)];
    }

    #[Test]
    public function get_sql_declaration(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->once())
            ->method('getBinaryTypeDeclarationSQL')
            ->with([
                'length' => 3,
                'fixed' => true,
            ]);

        $this->type->getSqlDeclaration(['length' => 3], $platform);
    }
}
