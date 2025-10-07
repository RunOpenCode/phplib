<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Cache;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;

/**
 * @internal
 */
final class CachedClassMetadata implements ClassMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public string $class {
        get {
            return $this->decorated->class;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $final {
        get {
            return $this->decorated->final;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $abstract {
        get {
            return $this->decorated->abstract;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $readonly {
        get {
            return $this->decorated->readonly;
        }
    }

    /**
     * {@inheritdoc}
     */
    public array $properties {
        get {
            return $this->decorated->properties;
        }
    }

    /**
     * {@inheritdoc}
     */
    public array $methods {
        get {
            return $this->decorated->methods;
        }
    }

    /**
     * {@inheritdoc}
     */
    public \ReflectionClass $reflection {
        get {
            return $this->decorated->reflection;
        }
    }

    /**
     * {@inheritdoc}
     */
    public ?ClassMetadataInterface $parent {
        get {
            return $this->decorated->parent instanceof \RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface ? new CachedClassMetadata(
                $this->decorated->parent,
                $this->cache
            ) : null;
        }
    }

    public function __construct(
        private readonly ClassMetadataInterface $decorated,
        private readonly CacheItemPoolInterface $cache,
    ) {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $class): bool
    {
        // @phpstan-ignore return.type
        return $this->cached($class, 'has');
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $class): object
    {
        // @phpstan-ignore return.type
        return $this->cached($class, 'get');
    }

    /**
     * {@inheritdoc}
     */
    public function all(?string $class = null): array
    {
        // @phpstan-ignore return.type
        return null !== $class ? $this->cached($class, 'all') : $this->decorated->all();
    }

    /**
     * {@inheritdoc}
     */
    public function property(string $class): PropertyMetadataInterface
    {
        return new CachedPropertyMetadata(
            $this->cached($class, 'property'), // @phpstan-ignore-line
            $this->cache,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function properties(string $class): array
    {
        /** @var list<PropertyMetadataInterface> $properties */
        $properties = $this->cached($class, 'properties');
        $cache      = $this->cache;

        return \array_map(
            static fn(PropertyMetadataInterface $property): PropertyMetadataInterface => new CachedPropertyMetadata($property, $cache),
            $properties,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function method(string $class): MethodMetadataInterface
    {
        return new CachedMethodMetadata(
            $this->cached($class, 'method'), // @phpstan-ignore-line
            $this->cache,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function methods(string $class): array
    {
        /** @var list<MethodMetadataInterface> $methods */
        $methods = $this->cached($class, 'methods');
        $cache   = $this->cache;

        return \array_map(
            static fn(MethodMetadataInterface $method): MethodMetadataInterface => new CachedMethodMetadata($method, $cache),
            $methods,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function member(string $class): PropertyMetadataInterface|MethodMetadataInterface
    {
        /** @var PropertyMetadataInterface|MethodMetadataInterface $member */
        $member = $this->cached($class, 'member');

        return $member instanceof PropertyMetadataInterface
            ? new CachedPropertyMetadata($member, $this->cache)
            : new CachedMethodMetadata($member, $this->cache);
    }

    /**
     * {@inheritdoc}
     */
    public function members(string $class): array
    {
        /** @var list<PropertyMetadataInterface|MethodMetadataInterface> $members */
        $members = $this->cached($class, 'members');
        $cache   = $this->cache;

        return \array_map(
            static fn(PropertyMetadataInterface|MethodMetadataInterface $member): PropertyMetadataInterface|MethodMetadataInterface => $member instanceof PropertyMetadataInterface
                ? new CachedPropertyMetadata($member, $cache)
                : new CachedMethodMetadata($member, $cache),
            $members
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->decorated->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->decorated->count();
    }

    /**
     * Get cached value for given method and attribute class.
     *
     * @param class-string $class  Attribute type.
     * @param string       $method Method name to call on decorated instance.
     */
    private function cached(
        string $class,
        string $method,
    ): mixed {
        $key = \md5(\sprintf(
            '%s::%s::%s::%s',
            $this->decorated->class,
            $class,
            self::class,
            $method,
        ));

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set($this->decorated->{$method}($class));
            $this->cache->save($item);
        }

        return $item->get();
    }
}
