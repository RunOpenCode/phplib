<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

interface ReaderInterface
{
    /**
     * Get metadata for given class.
     *
     * @param \ReflectionClass<object>|class-string $class Class or class name to read metadata for.
     *
     * @return ClassMetadataInterface Metadata instance.
     */
    public function read(\ReflectionClass|string $class): ClassMetadataInterface;
}
