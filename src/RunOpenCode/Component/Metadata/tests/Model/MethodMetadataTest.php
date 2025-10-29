<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedMethodMetadata;
use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;
use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;
use RunOpenCode\Component\Metadata\Model\MethodMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class MethodMetadataTest extends TestCase
{
    #[Test]
    public function has_attribute(): void
    {
        $metadata = $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'publicMethod');

        $this->assertTrue($metadata->has(MethodMetadataStub\FooAttribute::class));
        $this->assertFalse($metadata->has(MethodMetadataStub\BarAttribute::class));
    }

    #[Test]
    public function get_attribute(): void
    {
        $metadata = $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod');

        // @phpstan-ignore-next-line
        $this->assertInstanceOf(MethodMetadataStub\FooAttribute::class, $metadata->get(MethodMetadataStub\FooAttribute::class));
        $this->assertSame('foo_private_method', $metadata->get(MethodMetadataStub\FooAttribute::class)->value);
    }

    #[Test]
    public function get_attribute_throws_exception_when_none_found(): void
    {
        $this->expectException(NotExistsException::class);

        $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod')->get(self::class);
    }

    #[Test]
    public function get_attribute_throws_exception_when_multiple_found(): void
    {
        $this->expectException(UnexpectedResultException::class);

        $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod')->get(MethodMetadataStub\BarAttribute::class);
    }

    #[Test]
    public function get_attributes(): void
    {
        $metadata = $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod');

        $this->assertCount(1, $metadata->all(MethodMetadataStub\FooAttribute::class));
        $this->assertCount(2, $metadata->all(MethodMetadataStub\BarAttribute::class));
        $this->assertCount(3, $metadata->all());

        $this->assertSame(['foo_private_method'], \array_map(
            static fn(MethodMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(MethodMetadataStub\FooAttribute::class),
        ));

        $this->assertSame(['bar_private_method_1', 'bar_private_method_2'], \array_map(
            static fn(MethodMetadataStub\BarAttribute $attr): string => $attr->value,
            $metadata->all(MethodMetadataStub\BarAttribute::class),
        ));

        $this->assertSame(['bar_private_method_1', 'foo_private_method', 'bar_private_method_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(MethodMetadataStub\BarAttribute|MethodMetadataStub\FooAttribute $attr): string => $attr->value,
            $metadata->all(),
        ));
    }

    #[Test]
    public function calls(): void
    {
        $instance = new MethodMetadataStub\FooClass();
        $metadata = $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'protectedMethod');

        $this->assertSame('foo::protectedMethod()', $metadata->call($instance));
    }

    #[Test]
    public function counts(): void
    {
        $this->assertCount(3, $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod'));
    }

    #[Test]
    public function iterates(): void
    {
        $metadata = $this->createMethodMetadata(MethodMetadataStub\FooClass::class, 'privateMethod');

        $this->assertSame(['bar_private_method_1', 'foo_private_method', 'bar_private_method_2'], \array_map(
            // @phpstan-ignore-next-line argument.type
            static fn(MethodMetadataStub\BarAttribute|MethodMetadataStub\FooAttribute $attr): string => $attr->value,
            [...$metadata->getIterator()],
        ));
    }

    #[Test]
    public function sleeps_and_wakes_up(): void
    {
        $instance   = new MethodMetadataStub\FooClass();
        $metadata   = MethodMetadata::create(MethodMetadataStub\FooClass::class, 'privateMethod');
        $serialized = \serialize($metadata);

        /** @var MethodMetadata $unserialized */
        $unserialized = \unserialize($serialized, ['allowed_classes' => true]);

        $this->assertSame('foo::privateMethod()', $unserialized->call($instance));
    }

    /**
     * @param class-string $class
     */
    private function createMethodMetadata(string $class, string $method): MethodMetadataInterface
    {
        return new CachedMethodMetadata(
            MethodMetadata::create($class, $method),
            new ArrayAdapter(storeSerialized: false)
        );
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Model\MethodMetadataStub;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FooAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BarAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

class FooClass
{
    #[FooAttribute('foo_public_method')]
    public function publicMethod(): string
    {
        return 'foo::publicMethod()';
    }

    #[FooAttribute('foo_protected_method')]
    protected function protectedMethod(): string
    {
        return 'foo::protectedMethod()';
    }

    // @phpstan-ignore-next-line method.unused
    #[BarAttribute('bar_private_method_1')]
    #[FooAttribute('foo_private_method')]
    #[BarAttribute('bar_private_method_2')]
    private function privateMethod(): string
    {
        return 'foo::privateMethod()';
    }
}
