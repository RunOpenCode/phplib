<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Model;

use RunOpenCode\Component\Metadata\Collector\MembersCollector;
use RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;
use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;
use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;

/**
 * {@internal}
 */
final readonly class ClassMetadata extends AbstractMetadata implements ClassMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public string $class;

    /**
     * {@inheritdoc}
     */
    public bool $final;

    /**
     * {@inheritdoc}
     */
    public bool $abstract;

    /**
     * {@inheritdoc}
     */
    public bool $readonly;

    /**
     * {@inheritdoc}
     */
    public array $properties;

    /**
     * {@inheritdoc}
     */
    public array $methods;

    public ?ClassMetadataInterface $parent;

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public function __construct(
        public \ReflectionClass $reflection,
    ) {
        $this->class    = $this->reflection->getName();
        $this->final    = $this->reflection->isFinal();
        $this->abstract = $this->reflection->isAbstract();
        $this->readonly = $this->reflection->isReadOnly();
        $this->parent   = $this->reflection->getParentClass() ? new self($this->reflection->getParentClass()) : null;

        $this->properties = \array_values(\array_map(
            static fn(\ReflectionProperty $property): PropertyMetadata => new PropertyMetadata($property),
            \array_filter(
                MembersCollector::instance()->properties($this->reflection),
                static fn(\ReflectionProperty $property): bool => $property->getAttributes() !== [],
            )
        ));

        $this->methods = \array_values(\array_map(
            static fn(\ReflectionMethod $method): MethodMetadata => new MethodMetadata($method),
            \array_filter(
                MembersCollector::instance()->methods($this->reflection),
                static fn(\ReflectionMethod $method): bool => $method->getAttributes() !== [],
            )
        ));

        parent::__construct($this->reflection->getAttributes());
    }

    /**
     * Create new instance of class metadata for the given class.
     *
     * @param \ReflectionClass<object>|class-string $class Class reflection instance or class name.
     */
    public static function create(\ReflectionClass|string $class): self
    {
        return new self($class instanceof \ReflectionClass ? $class : new \ReflectionClass($class));
    }

    /**
     * {@inheritdoc}
     */
    public function property(string $class): PropertyMetadataInterface
    {
        $filtered = $this->properties($class);

        if (1 < \count($filtered)) {
            throw new UnexpectedResultException(\sprintf(
                'There are more then one (%d) properties with attribute of type "%s" found in "%s".',
                \count($filtered),
                $class,
                $this->reflection->getName(),
            ));
        }

        if (0 === \count($filtered)) {
            throw new NotExistsException(\sprintf(
                'Property with attribute of type "%s" could not be found in "%s".',
                $class,
                $this->reflection->getName(),
            ));
        }

        return $filtered[0];
    }

    /**
     * {@inheritdoc}
     */
    public function properties(string $class): array
    {
        return \array_values(\array_filter(
            $this->properties,
            static fn(PropertyMetadataInterface $metadata): bool => $metadata->has($class)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function method(string $class): MethodMetadataInterface
    {
        $filtered = $this->methods($class);

        if (1 < \count($filtered)) {
            throw new UnexpectedResultException(\sprintf(
                'There are more then one (%d) methods with attribute of type "%s" found in "%s".',
                \count($filtered),
                $class,
                $this->reflection->getName(),
            ));
        }

        if (0 === \count($filtered)) {
            throw new NotExistsException(\sprintf(
                'Method with attribute of type "%s" could not be found in "%s".',
                $class,
                $this->reflection->getName(),
            ));
        }

        return $filtered[0];
    }

    /**
     * {@inheritdoc}
     */
    public function methods(string $class): array
    {
        return \array_values(\array_filter(
            $this->methods,
            static fn(MethodMetadataInterface $metadata): bool => $metadata->has($class)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function member(string $class): PropertyMetadataInterface|MethodMetadataInterface
    {
        $filtered = $this->members($class);

        if (1 < \count($filtered)) {
            throw new UnexpectedResultException(\sprintf(
                'There are more then one (%d) member with attribute of type "%s" found in "%s".',
                \count($filtered),
                $class,
                $this->reflection->getName(),
            ));
        }

        if (0 === \count($filtered)) {
            throw new NotExistsException(\sprintf(
                'Member with attribute of type "%s" could not be found in "%s".',
                $class,
                $this->reflection->getName(),
            ));
        }

        return $filtered[0];
    }

    /**
     * {@inheritdoc}
     */
    public function members(string $class): array
    {
        return \array_values(\array_filter(
            [
                ...$this->properties,
                ...$this->methods,
            ],
            static fn(PropertyMetadataInterface|MethodMetadataInterface $metadata): bool => $metadata->has($class)
        ));
    }

    public function __sleep(): array
    {
        $vars = \get_object_vars($this);

        unset($vars['reflection']);

        return \array_keys($vars);
    }

    public function __wakeup(): void
    {
        /**
         * @noinspection             PhpSecondWriteToReadonlyPropertyInspection
         * @phpstan-ignore-next-line property.readOnlyAssignNotInConstructor
         */
        $this->reflection = new \ReflectionClass($this->class);
    }
}
