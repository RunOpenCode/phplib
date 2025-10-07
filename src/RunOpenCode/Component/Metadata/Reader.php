<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Metadata\Cache\CachedClassMetadata;
use RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\ReaderInterface;
use RunOpenCode\Component\Metadata\Model\ClassMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final readonly class Reader implements ReaderInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(
        ?CacheItemPoolInterface $cache,
    ) {
        $this->cache = $cache ?? new ArrayAdapter(storeSerialized: false);
    }

    public function read(\ReflectionClass|string $class): ClassMetadataInterface
    {
        $reflection = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
        $key        = \md5(\sprintf('%s::%s', $reflection->getName(), self::class));

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $metadata = new ClassMetadata($reflection);

            $item->set($metadata);
            $this->cache->save($item);
        }

        // @phpstan-ignore-next-line argument.type
        return new CachedClassMetadata($item->get(), $this->cache);
    }
}
