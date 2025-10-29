<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

/**
 * Method metadata descriptor.
 */
interface MethodMetadataInterface extends MetadataInterface
{
    /**
     * Get method name.
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
     * Check if method is static.
     */
    public bool $static {
        get;
    }

    /**
     * Check if method is public.
     */
    public bool $public {
        get;
    }

    /**
     * Check if method is protected.
     */
    public bool $protected {
        get;
    }

    /**
     * Check if method is private.
     */
    public bool $private {
        get;
    }

    /**
     * Get reflection method which contains method metadata.
     */
    public \ReflectionMethod $reflection {
        get;
    }

    /**
     * Call method with given arguments.
     *
     * @param object $object  Instance of object for which method should be invoked.
     * @param mixed  ...$args Method arguments.
     *
     * @return mixed Method return value.
     */
    public function call(object $object, mixed ...$args): mixed;
}
