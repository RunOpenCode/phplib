<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;

/**
 * Metadata reader.
 *
 * @phpstan-type TClassSource = \ReflectionClass<object>|object|class-string
 */
interface ReaderInterface
{
    /**
     * Get metadata for given class.
     *
     * @param TClassSource $class Class to read metadata.
     *
     * @return ClassMetadataInterface Metadata instance.
     */
    public function read(object|string $class): ClassMetadataInterface;

    /**
     * Check if attribute of given type is present in the metadata of the class.
     *
     * @param TClassSource         $class     Class to read metadata.
     * @param class-string<object> $attribute Attribute type.
     */
    public function has(object|string $class, string $attribute): bool;

    /**
     * Get attribute associated to the metadata.
     *
     * @template T of object
     *
     * @param TClassSource    $class     Class to read metadata.
     * @param class-string<T> $attribute Attribute type.
     *
     * @return T Instance of associated attribute.
     *
     * @throws UnexpectedResultException If there are more then one attribute of given type associated to the metadata.
     * @throws NotExistsException If there is no attribute of given type associated to the to metadata.
     */
    public function get(object|string $class, string $attribute): object;

    /**
     * Get attributes associated to the metadata of the class.
     *
     * @template T of object
     *
     * @param TClassSource         $class     Class to read metadata.
     * @param class-string<T>|null $attribute Optionally, filter attributes by type.
     *
     * @return ($attribute is null ? list<object> : list<T>) List of associated attributes.
     */
    public function all(object|string $class, ?string $attribute = null): array;

    /**
     * Get property metadata with associated attribute from given class.
     *
     * @param TClassSource $class     Class to read metadata.
     * @param class-string $attribute Attribute type.
     *
     * @throws NotExistsException If property with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one property with associated attribute.
     */
    public function property(object|string $class, string $attribute): PropertyMetadataInterface;

    /**
     * Get list of properties metadata with associated attribute from given class.
     *
     * @param TClassSource $class     Class to read metadata.
     * @param class-string $attribute Attribute type.
     *
     * @return list<PropertyMetadataInterface> List of properties metadata with associated attribute.
     */
    public function properties(object|string $class, string $attribute): array;

    /**
     * Get method metadata with associated attribute from given class.
     *
     * @param TClassSource $class     Class to read metadata.
     * @param class-string $attribute Attribute type.
     *
     * @throws NotExistsException If method with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one method with associated attribute.
     */
    public function method(object|string $class, string $attribute): MethodMetadataInterface;

    /**
     * Get list of methods metadata with associated attribute.
     *
     * @param TClassSource $class     Class to read metadata from given class.
     * @param class-string $attribute Attribute type.
     *
     * @return list<MethodMetadataInterface> List of methods metadata with associated attribute.
     */
    public function methods(object|string $class, string $attribute): array;

    /**
     * Get property or method metadata with associated attribute from given class.
     *
     * @param TClassSource $class     Class to read metadata from given class.
     * @param class-string $attribute Attribute type.
     *
     * @throws NotExistsException If neither property nor method with associated attribute does not exist.
     * @throws UnexpectedResultException If there are more then one property or method with associated attribute.
     */
    public function member(object|string $class, string $attribute): PropertyMetadataInterface|MethodMetadataInterface;

    /**
     * Get list of properties and methods metadata with associated attribute from given class.
     *
     * @param TClassSource $class     Class to read metadata from given class.
     * @param class-string $attribute Attribute type.
     *
     * @return list<PropertyMetadataInterface|MethodMetadataInterface> List of properties and methods metadata with associated attribute.
     */
    public function members(object|string $class, string $attribute): array;
}
