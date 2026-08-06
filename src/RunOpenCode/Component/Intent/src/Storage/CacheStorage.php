<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Storage;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Exception\NotExistsException;
use RunOpenCode\Component\Intent\Exception\RuntimeException;
use Symfony\Component\Uid\Ulid;

/**
 * Stores intents within any PSR-6 cache pool.
 *
 * Expiration is delegated to the pool itself, while the moment from which an intent
 * becomes valid is carried within the stored value, since PSR-6 has no notion of it.
 *
 * @phpstan-type Envelope = array{
 *     intent: object,
 *     valid_from: \DateTimeInterface,
 * }
 */
final readonly class CacheStorage implements IntentStorageInterface
{
    public function __construct(
        public CacheItemPoolInterface $pool,
    ) {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function store(object $intent, int $ttl = 86400, ?\DateTimeInterface $from = null): Ulid
    {
        $identifier = new Ulid();
        $from       = $from ? clone $from : new \DateTimeImmutable('now');
        $to         = clone $from;

        if (!$to instanceof \DateTimeImmutable) {
            $to = \DateTimeImmutable::createFromMutable($to);
        }

        /** @var \DateTimeImmutable $to */
        $to   = $to->add(new \DateInterval(\sprintf('PT%sS', $ttl)));
        $item = $this->pool->getItem((string)$identifier);

        $item->set([
            'intent'     => $intent,
            'valid_from' => $from,
        ]);

        $item->expiresAt($to);

        $this->pool->save($item);

        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function has(\Stringable|string|Ulid $identifier): bool
    {
        $identifier = (string)$identifier;
        $item       = $this->pool->getItem($identifier);

        if (!$item->isHit()) {
            return false;
        }

        /** @var Envelope $envelope */
        $envelope = $item->get();

        return $envelope['valid_from'] <= new \DateTimeImmutable('now');
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Ulid|\Stringable|string $identifier, bool $invalidate = true): object
    {
        $identifier = (string)$identifier;
        $item       = $this->pool->getItem($identifier);

        if (!$item->isHit()) {
            throw new NotExistsException(\sprintf('There is no intent stored under key "%s".', $identifier));
        }

        /** @var Envelope $envelope */
        $envelope = $item->get();

        if ($envelope['valid_from'] > new \DateTimeImmutable('now')) {
            throw new NotExistsException(\sprintf('There is no intent stored under key "%s".', $identifier));
        }

        // @phpstan-ignore-next-line function.alreadyNarrowedType
        if (!\is_object($envelope['intent'])) {
            throw new RuntimeException(\sprintf(
                'Intent stored under key "%s" could not be restored.',
                $identifier,
            ));
        }

        if ($invalidate) {
            $this->invalidate($identifier);
        }

        return $envelope['intent'];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(Ulid|\Stringable|string $identifier): void
    {
        $this->pool->deleteItem((string)$identifier);
    }

    /**
     * {@inheritdoc}
     *
     * Cache pools evict expired items on their own, so there is nothing to do here.
     */
    public function maintenance(): void
    {
        // noop.
    }
}
