<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Model;

use RunOpenCode\Component\Metadata\Contract\MetadataInterface;
use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;

/**
 * {@internal}
 */
abstract readonly class AbstractMetadata implements MetadataInterface
{
    /**
     * @var list<object>
     */
    private array $attributes;

    /**
     * @param list<\ReflectionAttribute<object>> $attributes
     */
    protected function __construct(
        array $attributes,
    ) {
        $this->attributes = \array_map(
            static fn(\ReflectionAttribute $reflectionAttribute): object => $reflectionAttribute->newInstance(),
            $attributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $class): bool
    {
        return \array_any(
            $this->attributes,
            static fn($attribute): bool => $attribute instanceof $class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $class): object
    {
        $filtered = $this->all($class);

        if (1 === \count($filtered)) {
            return $filtered[0];
        }

        if (0 === \count($filtered)) {
            throw new NotExistsException(\sprintf(
                'Attribute of type "%s" could not be found in "%s".',
                $class,
                self::class,
            ));
        }

        throw new UnexpectedResultException(\sprintf(
            'There are more then one (%d) attributes of type "%s" found in "%s".',
            \count($filtered),
            $class,
            self::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function all(?string $class = null): array
    {
        if (null === $class) {
            return $this->attributes;
        }

        return \array_values(\array_filter(
            $this->attributes,
            static fn(object $attribute): bool => $attribute instanceof $class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        yield from $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->attributes);
    }
}
