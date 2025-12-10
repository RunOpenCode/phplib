<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Tests\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Bitmask\Dbal\Type\AbstractEnumBitmaskType;
use RunOpenCode\Component\Bitmask\Dbal\Type\BitmaskType;
use RunOpenCode\Component\Bitmask\Model\Bitmask;

final class AbstractEnumBitmaskTypeTest extends TestCase
{
    private FooEnumBitmaskTypeType $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new FooEnumBitmaskTypeType(new BitmaskType());
    }

    /**
     * @param list<FooEnum>|null  $enums
     * @param non-empty-string|null $expected
     */
    #[Test]
    #[DataProvider('get_data')]
    public function convert_to_database_value(?array $enums, ?string $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($enums, $this->createStub(AbstractPlatform::class)));
    }

    /**
     * @param list<FooEnum>|null  $expected
     * @param non-empty-string|null $mask
     */
    #[Test]
    #[DataProvider('get_data')]
    public function convert_to_php_value(?array $expected, ?string $mask): void
    {
        $this->assertSame($expected, $this->type->convertToPHPValue($mask, $this->createStub(AbstractPlatform::class)));
    }

    /**
     * @return iterable<string, array{?list<FooEnum>, ?non-empty-string}>
     */
    public static function get_data(): iterable
    {
        yield 'Null.' => [null, null];
        yield 'FooEnum::Foo' => [[FooEnum::Foo], Bitmask::string('01000000')->toBinary()];
        yield 'FooEnum::Foo, FooEnum::Bar, FooEnum::Baz' => [[FooEnum::Foo, FooEnum::Bar, FooEnum::Baz], Bitmask::string('01010001')->toBinary()];
    }

    #[Test]
    public function get_sql_declaration(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->once())
            ->method('getBinaryTypeDeclarationSQL')
            ->with([
                'length' => 1,
                'fixed' => true,
            ]);

        $this->type->getSqlDeclaration([], $platform);
    }
}

/**
 * @extends AbstractEnumBitmaskType<FooEnum>
 */
final class FooEnumBitmaskTypeType extends AbstractEnumBitmaskType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnum(): string
    {
        return FooEnum::class;
    }
}

enum FooEnum: int
{
    case Foo = 1;
    case Bar = 3;
    case Baz = 7;
}
