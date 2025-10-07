<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

/**
 * Property metadata descriptor.
 */
interface PropertyMetadataInterface extends MetadataInterface
{
    /**
     * Get property name.
     */
    public string $name {
        get;
    }

    /**
     * Get declaring class name.
     *
     * @return class-string<object> Name of the class where property is declared.
     */
    public string $class {
        get;
    }

    /**
     * Check if property is static.
     */
    public bool $static {
        get;
    }

    /**
     * Check if property is public.
     */
    public bool $public {
        get;
    }

    /**
     * Check if property is protected.
     */
    public bool $protected {
        get;
    }

    /**
     * Check if property is private.
     */
    public bool $private {
        get;
    }

    /**
     * Check if property is readonly.
     */
    public bool $readonly {
        get;
    }

    /**
     * Get reflection property which contains property metadata.
     */
    public \ReflectionProperty $reflection {
        get;
    }

    /**
     * Check if object property value is initialized.
     *
     * @param object $object Instance of object on which property should be checked.
     *
     * @return bool True if property value is initialized, false otherwise.
     */
    public function initialized(object $object): bool;

    /**
     * Read property value from given object using reflection.
     *
     * If value is not initialized, null is returned.
     *
     * @param object $object Instance of object from which property value should be read.
     *
     * @return mixed Value of the property.
     */
    public function read(object $object): mixed;

    /**
     * Write property value to given object using reflection.
     *
     * @param object $object Instance of object on which property value should be written.
     * @param mixed  $value  Value to be set.
     */
    public function write(object $object, mixed $value): void;
}
