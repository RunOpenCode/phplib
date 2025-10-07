<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedClassMetadata;
use RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;
use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;
use RunOpenCode\Component\Metadata\Model\ClassMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ClassMetadataTest extends TestCase
{
    #[Test]
    public function has_class_attribute(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BarClass::class);

        $this->assertTrue($metadata->has(ClassMetadataStub\BarAttribute::class));
        $this->assertFalse($metadata->has(ClassMetadataStub\FooAttribute::class));
        $this->assertTrue($metadata->parent?->has(ClassMetadataStub\FooAttribute::class));
    }

    #[Test]
    public function get_class_attribute(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BarClass::class);

        $this->assertInstanceOf(ClassMetadataStub\BarAttribute::class, $metadata->get(ClassMetadataStub\BarAttribute::class));
        $this->assertSame('bar_class', $metadata->get(ClassMetadataStub\BarAttribute::class)->value);
        $this->assertInstanceOf(ClassMetadataStub\FooAttribute::class, $metadata->parent?->get(ClassMetadataStub\FooAttribute::class));
        $this->assertSame('foo_class', $metadata->parent->get(ClassMetadataStub\FooAttribute::class)->value);
    }

    #[Test]
    public function get_class_attribute_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createClassMetadata(ClassMetadataStub\BarClass::class)->get(self::class);
    }

    #[Test]
    public function get_class_attribute_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)->get(ClassMetadataStub\FooAttribute::class);
    }

    #[Test]
    public function get_class_attributes(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);

        $this->assertCount(2, $metadata->all(ClassMetadataStub\FooAttribute::class));
        $this->assertCount(1, $metadata->all(ClassMetadataStub\BarAttribute::class));
        $this->assertCount(3, $metadata->all());

        $this->assertSame(['baz_class_1', 'baz_class_2'], \array_map(
            static fn(ClassMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(ClassMetadataStub\FooAttribute::class),
        ));

        $this->assertSame(['baz_class_3'], \array_map(
            static fn(ClassMetadataStub\BarAttribute $attr): string => $attr->value,
            $metadata->all(ClassMetadataStub\BarAttribute::class),
        ));

        $this->assertSame(['baz_class_1', 'baz_class_3', 'baz_class_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(ClassMetadataStub\BarAttribute|ClassMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(),
        ));
    }

    #[Test]
    public function get_property(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $property = $metadata->property(ClassMetadataStub\FooAttribute::class);

        $this->assertSame('publicProperty', $property->name);
        $this->assertSame(ClassMetadataStub\BarClass::class, $property->class);
    }

    #[Test]
    public function get_property_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->property(self::class);
    }

    #[Test]
    public function get_property_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->property(ClassMetadataStub\BarAttribute::class);
    }

    #[Test]
    public function get_properties(): void
    {
        $metadata   = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $properties = $metadata->properties(ClassMetadataStub\BarAttribute::class);

        $this->assertCount(3, $properties);

        $this->assertSame([
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\FooClass::$privateProperty',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BarClass::$privateProperty',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BazClass::$privateProperty',
        ], \array_map(
            static fn(PropertyMetadataInterface $current): string => \sprintf('%s::$%s', $current->class, $current->name),
            $properties,
        ));
    }

    #[Test]
    public function get_method(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $method   = $metadata->method(ClassMetadataStub\FooAttribute::class);

        $this->assertSame('publicMethod', $method->name);
        $this->assertSame(ClassMetadataStub\BarClass::class, $method->class);
    }

    #[Test]
    public function get_method_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->method(self::class);
    }

    #[Test]
    public function get_method_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->method(ClassMetadataStub\BarAttribute::class);
    }

    #[Test]
    public function get_methods(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $methods  = $metadata->methods(ClassMetadataStub\BarAttribute::class);

        $this->assertCount(3, $methods);

        $this->assertSame([
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\FooClass::privateMethod()',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BarClass::privateMethod()',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BazClass::privateMethod()',
        ], \array_map(
            static fn(MethodMetadataInterface $current): string => \sprintf('%s::%s()', $current->class, $current->name),
            $methods,
        ));
    }

    #[Test]
    public function get_member(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $method   = $metadata->member(ClassMetadataStub\BazAttribute::class);

        $this->assertSame('privateMethod', $method->name);
        $this->assertSame(ClassMetadataStub\BazClass::class, $method->class);
    }

    #[Test]
    public function get_member_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->member(self::class);
    }

    #[Test]
    public function get_member_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createClassMetadata(ClassMetadataStub\BazClass::class)
             ->member(ClassMetadataStub\FooAttribute::class);
    }

    #[Test]
    public function get_members(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);
        $methods  = $metadata->members(ClassMetadataStub\BarAttribute::class);

        $this->assertCount(6, $methods);

        $this->assertSame([
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\FooClass::$privateProperty',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BarClass::$privateProperty',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BazClass::$privateProperty',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\FooClass::privateMethod()',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BarClass::privateMethod()',
            'RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub\BazClass::privateMethod()',
        ], \array_map(
            static fn(PropertyMetadataInterface|MethodMetadataInterface $current): string => $current instanceof MethodMetadataInterface
                ? \sprintf('%s::%s()', $current->class, $current->name)
                : \sprintf('%s::$%s', $current->class, $current->name),
            $methods,
        ));
    }

    #[Test]
    public function counts(): void
    {
        $this->assertCount(3, $this->createClassMetadata(ClassMetadataStub\BazClass::class));
    }

    #[Test]
    public function iterates(): void
    {
        $metadata = $this->createClassMetadata(ClassMetadataStub\BazClass::class);

        $this->assertSame(['baz_class_1', 'baz_class_3', 'baz_class_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(ClassMetadataStub\BarAttribute|ClassMetadataStub\FooAttribute $attr): string => $attr->value,
            [...$metadata->getIterator()],
        ));
    }

    #[Test]
    public function sleeps_and_wakes_up(): void
    {
        $metadata   = ClassMetadata::create(ClassMetadataStub\BazClass::class);
        $serialized = \serialize($metadata);

        /** @var ClassMetadata $unserialized */
        $unserialized = \unserialize($serialized, ['allowed_classes' => true]);

        $this->assertCount(4, $unserialized->properties);
        $this->assertCount(4, $unserialized->methods);
    }

    /**
     * @param class-string<object> $class
     */
    private function createClassMetadata(string $class): ClassMetadataInterface
    {
        return new CachedClassMetadata(
            ClassMetadata::create($class),
            new ArrayAdapter(storeSerialized: false)
        );
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Model\ClassMetadataStub;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FooAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BarAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BazAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[FooAttribute(value: 'foo_class')]
class FooClass
{
    #[FooAttribute('foo_public_property')]
    public string $publicProperty = 'foo::public';

    #[BarAttribute('foo_private_property')]
    // @phpstan-ignore-next-line property.unused
    private string $privateProperty = 'foo::private';

    #[FooAttribute('foo_public_method')]
    public function publicMethod(): string
    {
        return 'foo::public';
    }

    // @phpstan-ignore-next-line method.unused
    #[BarAttribute('foo_private_method')]
    private function privateMethod(): string
    {
        return 'foo::private';
    }
}

#[BarAttribute(value: 'bar_class')]
class BarClass extends FooClass
{
    #[FooAttribute('foo_public_property')]
    public string $publicProperty = 'bar::public';

    #[BarAttribute('foo_private_property')]
    // @phpstan-ignore-next-line property.unused
    private string $privateProperty = 'bar::private';

    #[FooAttribute('foo_public_method')]
    #[\Override]
    public function publicMethod(): string
    {
        return 'foo::public';
    }

    // @phpstan-ignore-next-line method.unused
    #[BarAttribute('foo_private_method')]
    private function privateMethod(): string
    {
        return 'bar::private';
    }
}

#[FooAttribute(value: 'baz_class_1')]
#[BarAttribute(value: 'baz_class_3')]
#[FooAttribute(value: 'baz_class_2')]
class BazClass extends BarClass
{
    #[BarAttribute('foo_private_property')]
    // @phpstan-ignore-next-line property.unused
    private string $privateProperty = 'baz::private';

    // @phpstan-ignore-next-line method.unused
    #[BarAttribute('foo_private_method')]
    #[BazAttribute('baz_private_method')]
    private function privateMethod(): string
    {
        return 'baz::private';
    }
}
