<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Contract;

use RunOpenCode\Component\Intent\Exception\NotExistsException;
use Symfony\Component\Uid\Ulid;

/**
 * Storage layer for intents.
 */
interface IntentStorageInterface
{
    /**
     * Store some intent into storage and retrieve its temporary identifier.
     *
     * @param object                  $intent Intent to store. Can be any object, serializable.
     * @param int                     $ttl    How long in seconds intent object should be stored.
     * @param \DateTimeInterface|null $from   You may, optionally, allow access to intent from some date/time in the future.
     *
     * @return Ulid Intent temporary identifier.
     */
    public function store(object $intent, int $ttl = 86400, ?\DateTimeInterface $from = null): Ulid;

    /**
     * Check if intent for given identifier exists.
     *
     * @param Ulid|\Stringable|string $identifier Identifier for which  intent should be checked for.
     *
     * @return bool TRUE if intent exists and can be retrieved.
     */
    public function has(Ulid|\Stringable|string $identifier): bool;

    /**
     * Fetch intent for given identifier.
     *
     * @param Ulid|\Stringable|string $identifier Identifier for which  intent should be fetched.
     * @param bool   $invalidate Should intent be invalidated after fetch. Defaults to TRUE.
     *
     * @throws NotExistsException
     *
     */
    public function fetch(Ulid|\Stringable|string $identifier, bool $invalidate = true): object;

    /**
     * Invalidates intent with given identifier. Does not throw exception
     * if intent with given identifier does not exists.
     *
     * @param Ulid|string $identifier Identifier for which  intent should be invalidated.
     */
    public function invalidate(Ulid|\Stringable|string $identifier): void;

    /**
     * Remove all expired intents.
     */
    public function maintenance(): void;
}
