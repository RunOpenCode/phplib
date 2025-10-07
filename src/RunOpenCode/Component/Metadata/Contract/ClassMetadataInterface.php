<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;

/**
 * Class metadata descriptor.
 */
interface ClassMetadataInterface extends MetadataInterface
{
    /**
     * Get class name.
     *
     * @return class-string<object> Name of the class.
     */
    public string $class {
        get;
    }

    /**
     * Check if class is final.
     */
    public bool $final {
        get;
    }

    /**
     * Check if class is abstract
     */
    public bool $abstract {
        get;
    }

    /**
     * Check if class is readonly.
     */
    public bool $readonly {
        get;
    }

    /**
     * Get parent class metadata or null if class does not have parent.
     */
    public ClassMetadataInterface|null $parent {
        get;
    }

    /**
     * Get list of properties metadata.
     *
     * @var list<PropertyMetadataInterface> List of properties metadata.
     */
    public array $properties {
        get;
    }

    /**
     * Get list of methods metadata.
     *
     * @var list<MethodMetadataInterface> List of methods metadata.
     */
    public array $methods {
        get;
    }

    /**
     * Get reflection class which contains method metadata.
     *
     * @var \ReflectionClass<object>
     */
    public \ReflectionClass $reflection {
        get;
    }

    /**
     * Get property metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @throws NotExistsException If property with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one property with associated attribute.
     */
    public function property(string $class): PropertyMetadataInterface;

    /**
     * Get list of properties metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @return list<PropertyMetadataInterface> List of properties metadata with associated attribute.
     */
    public function properties(string $class): array;

    /**
     * Get method metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @throws NotExistsException If method with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one method with associated attribute.
     */
    public function method(string $class): MethodMetadataInterface;

    /**
     * Get list of methods metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @return list<MethodMetadataInterface> List of methods metadata with associated attribute.
     */
    public function methods(string $class): array;

    /**
     * Get property or method metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @throws NotExistsException If neither property nor method with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one property or method with associated attribute.
     */
    public function member(string $class): PropertyMetadataInterface|MethodMetadataInterface;

    /**
     * Get list of properties and methods metadata with associated attribute.
     *
     * @param class-string $class Attribute type.
     *
     * @return list<PropertyMetadataInterface|MethodMetadataInterface> List of properties and methods metadata with associated attribute.
     */
    public function members(string $class): array;
}
