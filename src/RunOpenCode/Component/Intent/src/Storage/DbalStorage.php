<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Exception\NotExistsException;
use RunOpenCode\Component\Intent\Exception\RuntimeException;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * Stores intents within a database table using Doctrine Dbal.
 */
final readonly class DbalStorage implements IntentStorageInterface
{
    /**
     * @param non-empty-string $tableName
     */
    public function __construct(
        public Connection $connection,
        public string     $tableName = 'runopencode_intent',
    ) {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function store(object $intent, int $ttl = 86400, ?\DateTimeInterface $from = null): Ulid
    {
        $identifier = new Ulid();

        $from = $from ? clone $from : new \DateTimeImmutable('now');
        $to   = clone $from;

        if (!$to instanceof \DateTimeImmutable) {
            $to = \DateTimeImmutable::createFromMutable($to);
        }

        /** @var \DateTimeImmutable $to */
        $to = $to->add(new \DateInterval(\sprintf('PT%sS', $ttl)));

        $this->connection->insert($this->tableName, [
            'id'         => $identifier,
            'intent'     => \serialize($intent),
            'valid_from' => $from,
            'expires_at' => $to,
            'created_at' => new \DateTimeImmutable('now'),
        ], [
            'id'         => UlidType::NAME,
            'valid_from' => Types::DATETIME_IMMUTABLE,
            'expires_at' => Types::DATETIME_IMMUTABLE,
            'created_at' => Types::DATETIME_IMMUTABLE,
        ]);

        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Ulid|\Stringable|string $identifier, bool $invalidate = true): object
    {
        $identifier = $identifier instanceof Ulid ? $identifier : Ulid::fromString((string)$identifier);

        $row = $this->connection->executeQuery(\sprintf(
            'SELECT * FROM %s WHERE id = :id LIMIT 1',
            $this->tableName,
        ), [
            'id' => $identifier,
        ], [
            'id' => UlidType::NAME,
        ])->fetchAssociative() ?: null;

        if (null === $row) {
            throw new NotExistsException(\sprintf('There is no intent stored under key "%s".', $identifier));
        }

        $platform = $this->connection->getDatabasePlatform();
        /** @var \DateTimeImmutable $from */
        $from = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['valid_from'], $platform);
        /** @var \DateTimeImmutable $expiresAt */
        $expiresAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['expires_at'], $platform);

        $now = new \DateTimeImmutable('now');

        // Expired intent is of no use to anybody anymore, so it is invalidated on the spot.
        if ($expiresAt < $now) {
            $this->invalidate($identifier);

            throw new NotExistsException(\sprintf('There is no intent stored under key "%s".', $identifier));
        }

        // Intent exists, but is not valid yet — it must be preserved until it becomes valid.
        if ($from > $now) {
            throw new NotExistsException(\sprintf('There is no intent stored under key "%s".', $identifier));
        }

        /** @var string $intent */
        $intent = $row['intent'];
        $object = \unserialize($intent, ['allowed_classes' => true]);

        if (!\is_object($object)) {
            throw new RuntimeException(\sprintf(
                'Intent stored under key "%s" could not be restored.',
                $identifier,
            ));
        }

        if ($invalidate) {
            $this->invalidate($identifier);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(Ulid|\Stringable|string $identifier): void
    {
        $identifier = $identifier instanceof Ulid ? $identifier : Ulid::fromString((string)$identifier);

        $this->connection->executeQuery(\sprintf('DELETE FROM %s WHERE id = :id', $this->tableName), [
            'id' => $identifier,
        ], [
            'id' => UlidType::NAME,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function maintenance(): void
    {
        $this->connection->executeQuery(\sprintf('DELETE FROM %s WHERE expires_at <= :now', $this->tableName), [
            'now' => new \DateTimeImmutable('now'),
        ], [
            'now' => Types::DATETIME_IMMUTABLE,
        ]);
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $connection = $eventArgs->getEntityManager()->getConnection();

        if ($connection !== $this->connection) {
            return;
        }

        $schema = $eventArgs->getSchema();
        $table  = $schema->createTable($this->tableName);

        $table->addColumn('id', UlidType::NAME);
        $table->addColumn('intent', Types::TEXT);
        $table->addColumn('valid_from', Types::DATETIME_IMMUTABLE);
        $table->addColumn('expires_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                                ->setUnquotedColumnNames('id')
                                ->create()
        );

        $table->addIndex(['expires_at']);
    }
}
