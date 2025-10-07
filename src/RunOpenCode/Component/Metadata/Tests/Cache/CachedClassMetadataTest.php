<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedClassMetadata;
use RunOpenCode\Component\Metadata\Model\ClassMetadata;
use RunOpenCode\Component\Metadata\Tests\PHPUnit\AssertMetadataTrait;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapterEvent;

final class CachedClassMetadataTest extends TestCase
{
    use AssertMetadataTrait;

    private TraceableAdapter $cache;

    private CachedClassMetadata $metadata;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache    = new TraceableAdapter(new ArrayAdapter());
        $this->metadata = new CachedClassMetadata(ClassMetadata::create(CachedClassMetadataStub\Foo::class), $this->cache);
    }

    /**
     * @param class-string $class
     *
     */
    #[Test]
    #[DataProvider('get_data_for_cached_calls')]
    public function cached_calls(string $method, string $class): void
    {
        $original = $this->metadata->{$method}($class);
        $cached   = $this->metadata->{$method}($class);

        $this->assertMetadataEquals($original, $cached);

        /** @var TraceableAdapterEvent[] $calls */
        $calls = $this->cache->getCalls();

        $this->assertCount(3, $calls);
        $this->assertSame(1, $calls[0]->misses);
        $this->assertSame('save', $calls[1]->name);
        $this->assertSame(1, $calls[2]->hits);
    }

    /**
     * @return iterable<string, array{string, class-string}>
     */
    public static function get_data_for_cached_calls(): iterable
    {
        yield 'has' => ['has', CachedClassMetadataStub\FooAttribute::class];
        yield 'get' => ['get', CachedClassMetadataStub\FooAttribute::class];
        yield 'all' => ['all', CachedClassMetadataStub\FooAttribute::class];
        yield 'property' => ['property', CachedClassMetadataStub\FooAttribute::class];
        yield 'properties' => ['properties', CachedClassMetadataStub\FooAttribute::class];
        yield 'method' => ['method', CachedClassMetadataStub\FooAttribute::class];
        yield 'methods' => ['methods', CachedClassMetadataStub\FooAttribute::class];
        yield 'member' => ['member', CachedClassMetadataStub\BarAttribute::class];
        yield 'members' => ['members', CachedClassMetadataStub\BarAttribute::class];
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Cache\CachedClassMetadataStub;

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
