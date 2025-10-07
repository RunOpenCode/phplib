<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedPropertyMetadata;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;
use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;
use RunOpenCode\Component\Metadata\Model\PropertyMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PropertyMetadataTest extends TestCase
{
    #[Test]
    public function has_attribute(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'publicProperty');

        $this->assertTrue($metadata->has(PropertyMetadataStub\FooAttribute::class));
        $this->assertFalse($metadata->has(PropertyMetadataStub\BarAttribute::class));
    }

    #[Test]
    public function get_attribute(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty');

        $this->assertInstanceOf(PropertyMetadataStub\FooAttribute::class, $metadata->get(PropertyMetadataStub\FooAttribute::class));
        $this->assertSame('foo_private_property', $metadata->get(PropertyMetadataStub\FooAttribute::class)->value);
    }

    #[Test]
    public function get_attribute_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty')->get(self::class);
    }

    #[Test]
    public function get_attribute_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty')->get(PropertyMetadataStub\BarAttribute::class);
    }

    #[Test]
    public function get_attributes(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty');

        $this->assertCount(1, $metadata->all(PropertyMetadataStub\FooAttribute::class));
        $this->assertCount(2, $metadata->all(PropertyMetadataStub\BarAttribute::class));
        $this->assertCount(3, $metadata->all());

        $this->assertSame(['foo_private_property'], \array_map(
            static fn(PropertyMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(PropertyMetadataStub\FooAttribute::class),
        ));

        $this->assertSame(['bar_private_property_1', 'bar_private_property_2'], \array_map(
            static fn(PropertyMetadataStub\BarAttribute $attr): string => $attr->value,
            $metadata->all(PropertyMetadataStub\BarAttribute::class),
        ));

        $this->assertSame(['bar_private_property_1', 'foo_private_property', 'bar_private_property_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(PropertyMetadataStub\BarAttribute|PropertyMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(),
        ));
    }

    #[Test]
    public function initialized(): void
    {
        $instance = new PropertyMetadataStub\FooClass();

        $this->assertFalse($this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty')->initialized($instance));
        $this->assertTrue($this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'protectedProperty')->initialized($instance));
    }

    #[Test]
    public function read(): void
    {
        $instance = new PropertyMetadataStub\FooClass();

        $this->assertNull($this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty')->read($instance));
        $this->assertSame('foo::protected', $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'protectedProperty')->read($instance));
    }

    #[Test]
    public function write(): void
    {
        $instance = new PropertyMetadataStub\FooClass();
        $metadata = $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty');

        $this->assertFalse($metadata->initialized($instance));
        $this->assertNull($metadata->read($instance));

        $metadata->write($instance, 'written');


        $this->assertTrue($metadata->initialized($instance));
        $this->assertSame('written', $metadata->read($instance));
    }

    #[Test]
    public function counts(): void
    {
        $this->assertCount(3, $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty'));
    }

    #[Test]
    public function iterates(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyMetadataStub\FooClass::class, 'privateProperty');

        $this->assertSame(['bar_private_property_1', 'foo_private_property', 'bar_private_property_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(PropertyMetadataStub\BarAttribute|PropertyMetadataStub\FooAttribute $attr): string => $attr->value,
            [...$metadata->getIterator()],
        ));
    }

    #[Test]
    public function sleeps_and_wakes_up(): void
    {
        $instance   = new PropertyMetadataStub\FooClass();
        $metadata   = PropertyMetadata::create(PropertyMetadataStub\FooClass::class, 'protectedProperty');
        $serialized = \serialize($metadata);

        /** @var PropertyMetadata $unserialized */
        $unserialized = \unserialize($serialized, ['allowed_classes' => true]);

        $this->assertSame('foo::protected', $unserialized->read($instance));
    }

    /**
     * @param class-string<object> $class
     */
    private function createPropertyMetadata(string $class, string $name): PropertyMetadataInterface
    {
        return new CachedPropertyMetadata(
            PropertyMetadata::create($class, $name),
            new ArrayAdapter(storeSerialized: false),
        );
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Model\PropertyMetadataStub;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class FooAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class BarAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

class FooClass
{
    #[FooAttribute('foo_public_property')]
    public string $publicProperty = 'foo::public';

    #[FooAttribute('foo_protected_property')]
    protected string $protectedProperty = 'foo::protected';

    #[BarAttribute('bar_private_property_1')]
    #[FooAttribute('foo_private_property')]
    #[BarAttribute('bar_private_property_2')]
    // @phpstan-ignore-next-line property.uninitializedReadonly property.unused
    private readonly string $privateProperty;
}
