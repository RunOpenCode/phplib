<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Metadata\Cache\CachedClassMetadata;
use RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\ReaderInterface;
use RunOpenCode\Component\Metadata\Model\ClassMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Default implementation of metadata reader with caching capabilities.
 */
final readonly class Reader implements ReaderInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(
        ?CacheItemPoolInterface $cache,
    ) {
        $this->cache = $cache ?? new ArrayAdapter(storeSerialized: false);
    }

    /**
     * {@inheritdoc}
     */
    public function read(object|string $class): ClassMetadataInterface
    {
        $reflection = $class instanceof \ReflectionClass ? $class : new \ReflectionClass(\is_string($class) ? $class : \get_class($class));
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

    /**
     * {@inheritdoc}
     */
    public function has(object|string $class, string $attribute): bool
    {
        return $this->read($class)->has($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function get(object|string $class, string $attribute): object
    {
        return $this->read($class)->get($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function all(object|string $class, ?string $attribute = null): array
    {
        return $this->read($class)->all($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function property(object|string $class, string $attribute): PropertyMetadataInterface
    {
        return $this->read($class)->property($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function properties(object|string $class, string $attribute): array
    {
        return $this->read($class)->properties($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function method(object|string $class, string $attribute): MethodMetadataInterface
    {
        return $this->read($class)->method($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function methods(object|string $class, string $attribute): array
    {
        return $this->read($class)->methods($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function member(object|string $class, string $attribute): PropertyMetadataInterface|MethodMetadataInterface
    {
        return $this->read($class)->member($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function members(object|string $class, string $attribute): array
    {
        return $this->read($class)->members($attribute);
    }
}
