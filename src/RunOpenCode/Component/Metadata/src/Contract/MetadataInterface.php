<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Contract;

use RunOpenCode\Component\Metadata\Exception\NotExistsException;
use RunOpenCode\Component\Metadata\Exception\UnexpectedResultException;

/**
 * Metadata descriptor.
 *
 * @extends \IteratorAggregate<int, object>
 */
interface MetadataInterface extends \IteratorAggregate, \Countable
{
    /**
     * Check if attribute of given type is present in the metadata.
     *
     * @param class-string<object> $class Attribute type.
     */
    public function has(string $class): bool;

    /**
     * Get attribute associated to the metadata.
     *
     * @template T of object
     *
     * @param class-string<T> $class Attribute type.
     *
     * @return T Instance of associated attribute.
     *
     * @throws UnexpectedResultException If there are more then one attribute of given type associated to the metadata.
     * @throws NotExistsException If there is no attribute of given type associated to the to metadata.
     */
    public function get(string $class): object;

    /**
     * Get attributes associated to the metadata.
     *
     * @template T of object
     *
     * @param class-string<T>|null $class Optionally, filter attributes by type.
     *
     * @return ($class is null ? list<object> : list<T>) List of associated attributes.
     */
    public function all(?string $class = null): array;
}
