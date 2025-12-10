<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Bundle\MetadataBundle\ReaderFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\ProxyAdapter;

final class ReaderFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('get_data_for_ignores_cache_pool_in_non_production_environment')]
    public function ignores_cache_pool_in_non_production_environment(string $environment): void
    {
        $pool   = $this->createStub(CacheItemPoolInterface::class);
        $reader = ReaderFactory::create($pool, $environment);
        $used   = (new \ReflectionClass($reader))->getProperty('cache')->getValue($reader);

        $this->assertNotSame($pool, $used);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function get_data_for_ignores_cache_pool_in_non_production_environment(): iterable
    {
        yield 'Dev environment.' => ['dev'];
        yield 'Test environment.' => ['test'];
        yield 'Custom environment.' => ['dev'];
    }

    #[Test]
    public function uses_cache_pool_stack_in_production_environment(): void
    {
        $pool   = $this->createStub(CacheItemPoolInterface::class);
        $reader = ReaderFactory::create($pool, 'prod');
        $used   = (new \ReflectionClass($reader))->getProperty('cache')->getValue($reader);

        $this->assertInstanceOf(ChainAdapter::class, $used);

        /** @var CacheItemPoolInterface[] $chained */
        $chained = (new \ReflectionClass($used))->getProperty('adapters')->getValue($used);

        $this->assertCount(2, $chained);
        $this->assertInstanceOf(ArrayAdapter::class, $chained[0]);
        $this->assertInstanceOf(ProxyAdapter::class, $chained[1]);
    }
}
