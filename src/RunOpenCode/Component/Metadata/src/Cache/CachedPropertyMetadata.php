<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Cache;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;

/**
 * @internal
 */
final class CachedPropertyMetadata implements PropertyMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public string $name {
        get {
            return $this->decorated->name;
        }
    }

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
    public bool $static {
        get {
            return $this->decorated->static;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $public {
        get {
            return $this->decorated->public;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $protected {
        get {
            return $this->decorated->protected;
        }
    }

    /**
     * {@inheritdoc}
     */
    public bool $private {
        get {
            return $this->decorated->private;
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
    public \ReflectionProperty $reflection {
        get {
            return $this->decorated->reflection;
        }
    }

    public function __construct(
        private readonly PropertyMetadataInterface $decorated,
        private readonly CacheItemPoolInterface    $cache,
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
    public function initialized(object $object): bool
    {
        return $this->decorated->initialized($object);
    }

    /**
     * {@inheritdoc}
     */
    public function read(object $object): mixed
    {
        return $this->decorated->read($object);
    }

    /**
     * {@inheritdoc}
     */
    public function write(object $object, mixed $value): void
    {
        $this->decorated->write($object, $value);
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

    private function cached(
        string $class,
        string $method,
    ): mixed {
        $key = \md5(\sprintf(
            '%s::$%s::%s::%s::%s',
            $this->decorated->class,
            $this->decorated->name,
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
