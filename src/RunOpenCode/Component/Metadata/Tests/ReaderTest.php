<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedClassMetadata;
use RunOpenCode\Component\Metadata\Cache\CachedMethodMetadata;
use RunOpenCode\Component\Metadata\Cache\CachedPropertyMetadata;
use RunOpenCode\Component\Metadata\Reader;
use RunOpenCode\Component\Metadata\Tests\ReaderStub\FooAttribute;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapterEvent;

final class ReaderTest extends TestCase
{
    private Reader $reader;

    private TraceableAdapter $cache;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache  = new TraceableAdapter(new ArrayAdapter());
        $this->reader = new Reader($this->cache);
    }

    #[Test]
    public function generates_cached_metadata_decorators(): void
    {
        $metadata = $this->reader->read(ReaderStub\Foo::class);

        $this->assertInstanceOf(CachedClassMetadata::class, $metadata);
        $this->assertInstanceOf(CachedPropertyMetadata::class, $metadata->property(FooAttribute::class));
        $this->assertInstanceOf(CachedMethodMetadata::class, $metadata->method(FooAttribute::class));

        $this->assertCount(3, \array_filter(
            \array_map(
                // @phpstan-ignore-next-line argument.type
                static fn(TraceableAdapterEvent $event): string => $event->name,
                $this->cache->getCalls(),
            ),
            static fn(string $name): bool => $name === 'save'
        ));
    }

    #[Test]
    public function has(): void
    {
        $this->assertTrue($this->reader->has(ReaderStub\Foo::class, ReaderStub\FooAttribute::class));
    }

    #[Test]
    public function get(): void
    {
        $this->assertSame('foo_class', $this->reader->get(ReaderStub\Foo::class, ReaderStub\FooAttribute::class)->value);
    }

    #[Test]
    public function all(): void
    {
        $attributes = $this->reader->all(ReaderStub\Foo::class, ReaderStub\FooAttribute::class);

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(FooAttribute::class, $attributes[0]); // @phpstan-ignore-line
        $this->assertSame('foo_class', $attributes[0]->value);
    }

    #[Test]
    public function property(): void
    {
        $this->assertSame('foo', $this->reader->property(ReaderStub\Foo::class, ReaderStub\FooAttribute::class)->name);
    }

    #[Test]
    public function properties(): void
    {
        $properties = $this->reader->properties(ReaderStub\Foo::class, ReaderStub\FooAttribute::class);

        $this->assertCount(1, $properties);
        $this->assertSame('foo', $properties[0]->name);
    }

    #[Test]
    public function method(): void
    {
        $this->assertSame('bar', $this->reader->method(ReaderStub\Foo::class, ReaderStub\FooAttribute::class)->name);
    }

    #[Test]
    public function methods(): void
    {
        $methods = $this->reader->methods(ReaderStub\Foo::class, ReaderStub\FooAttribute::class);

        $this->assertCount(1, $methods);
        $this->assertSame('bar', $methods[0]->name);
    }

    #[Test]
    public function member(): void
    {
        $this->assertSame('bar', $this->reader->member(ReaderStub\Foo::class, ReaderStub\BarAttribute::class)->name);
    }

    #[Test]
    public function members(): void
    {
        $members = $this->reader->members(ReaderStub\Foo::class, ReaderStub\BarAttribute::class);

        $this->assertCount(1, $members);
        $this->assertSame('bar', $members[0]->name);
    }
}

namespace RunOpenCode\Component\Metadata\Tests\ReaderStub;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FooAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class BarAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[FooAttribute('foo_class')]
class Foo
{
    #[FooAttribute('foo_property')]
    public string $foo = 'foo';

    #[FooAttribute('bar_method')]
    #[BarAttribute('bar_method')]
    public function bar(): void
    {
    }
}
