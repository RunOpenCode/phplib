<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Cache\CachedMethodMetadata;
use RunOpenCode\Component\Metadata\Model\MethodMetadata;
use RunOpenCode\Component\Metadata\Tests\PHPUnit\AssertMetadataTrait;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapterEvent;

final class CachedMethodMetadataTest extends TestCase
{
    use AssertMetadataTrait;

    private TraceableAdapter $cache;

    private CachedMethodMetadata $metadata;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache    = new TraceableAdapter(new ArrayAdapter());
        $this->metadata = new CachedMethodMetadata(MethodMetadata::create(CachedMethodMetadataStub\FooClass::class, 'publicMethod'), $this->cache);
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
        yield 'has' => ['has', CachedMethodMetadataStub\FooAttribute::class];
        yield 'get' => ['get', CachedMethodMetadataStub\FooAttribute::class];
        yield 'all' => ['all', CachedMethodMetadataStub\FooAttribute::class];
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Cache\CachedMethodMetadataStub;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FooAttribute
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
}
