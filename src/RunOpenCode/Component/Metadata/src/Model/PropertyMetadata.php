<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Model;

use RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface;

/**
 * {@internal}
 */
final readonly class PropertyMetadata extends AbstractMetadata implements PropertyMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public string $name;

    /**
     * {@inheritdoc}
     */
    public string $class;

    /**
     * {@inheritdoc}
     */
    public bool $static;

    /**
     * {@inheritdoc}
     */
    public bool $public;

    /**
     * {@inheritdoc}
     */
    public bool $protected;

    /**
     * {@inheritdoc}
     */
    public bool $private;

    /**
     * {@inheritdoc}
     */
    public bool $readonly;

    public function __construct(
        public \ReflectionProperty $reflection,
    ) {
        $this->name      = $this->reflection->getName();
        $this->class     = $this->reflection->getDeclaringClass()->getName();
        $this->static    = $this->reflection->isStatic();
        $this->public    = $this->reflection->isPublic();
        $this->protected = $this->reflection->isProtected();
        $this->private   = $this->reflection->isPrivate();
        $this->readonly  = $this->reflection->isReadOnly();

        parent::__construct($this->reflection->getAttributes());
    }

    /**
     * Create property metadata.
     *
     * @param class-string     $class    Class for which metadata is being created.
     * @param non-empty-string $property Property for which metadata is being created.
     */
    public static function create(string $class, string $property): self
    {
        return new self(new \ReflectionProperty($class, $property));
    }

    /**
     * {@inheritdoc}
     */
    public function initialized(object $object): bool
    {
        return $this->reflection->isInitialized($object);
    }

    /**
     * {@inheritdoc}
     */
    public function read(object $object): mixed
    {
        if ($this->reflection->isInitialized($object)) {
            return $this->reflection->getValue($object);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function write(object $object, mixed $value): void
    {
        $this->reflection->setValue($object, $value);
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
        $this->reflection = new \ReflectionProperty($this->class, $this->name);
    }
}
