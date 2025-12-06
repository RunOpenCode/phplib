<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Tests\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Bitmask\Dbal\Type\BitmaskDebugType;
use RunOpenCode\Component\Bitmask\Model\Bitmask;

final class BitmaskDebugTypeTest extends TestCase
{
    private BitmaskDebugType $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new BitmaskDebugType();
    }

    #[Test]
    public function get_name(): void
    {
        $this->assertSame('bitmask_debug', $this->type->getName());
    }

    #[Test]
    #[DataProvider('data_provider')]
    public function convert_to_database_value(Bitmask $mask, string $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($mask, $this->createMock(AbstractPlatform::class)));
    }

    #[Test]
    #[DataProvider('data_provider')]
    public function convert_to_php_value(Bitmask $expected, string $mask): void
    {
        $this->assertTrue($expected->equals(
            $this->type->convertToPHPValue($mask, $this->createMock(AbstractPlatform::class))
        ));
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
        yield '00000000' => [Bitmask::string('00000000'), '00000000'];
        yield '00000100' => [Bitmask::string('00000100'), '00000100'];
    }

    #[Test]
    public function get_sql_declaration_when_less_then_255_chars_required(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->once())
            ->method('getStringTypeDeclarationSQL')
            ->with([
                'length' => 24,
            ]);

        $this->type->getSqlDeclaration(['length' => 3], $platform);
    }

    #[Test]
    public function get_sql_declaration_when_more_then_255_chars_required(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->once())
            ->method('getClobTypeDeclarationSQL')
            ->with([
                'length' => 256,
            ]);

        $this->type->getSqlDeclaration(['length' => 32], $platform);
    }
}
