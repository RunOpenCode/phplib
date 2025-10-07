<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Model;

use RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface;

/**
 * {@internal}
 */
final readonly class MethodMetadata extends AbstractMetadata implements MethodMetadataInterface
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

    public function __construct(
        public \ReflectionMethod $reflection,
    ) {
        $this->name      = $this->reflection->getName();
        $this->class     = $this->reflection->getDeclaringClass()->getName();
        $this->static    = $this->reflection->isStatic();
        $this->public    = $this->reflection->isPublic();
        $this->protected = $this->reflection->isProtected();
        $this->private   = $this->reflection->isPrivate();

        parent::__construct($this->reflection->getAttributes());
    }


    /**
     * Create method metadata for given class and method name.
     *
     * @param class-string<object> $class  Name of the class.
     * @param string               $method Name of the method.
     */
    public static function create(string $class, string $method): self
    {
        return new self(new \ReflectionMethod($class, $method));
    }

    /**
     * {@inheritdoc}
     */
    public function call(object $object, ...$args): mixed
    {
        return $this->reflection->invokeArgs($object, $args);
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
        $this->reflection = new \ReflectionMethod($this->class, $this->name);
    }
}
