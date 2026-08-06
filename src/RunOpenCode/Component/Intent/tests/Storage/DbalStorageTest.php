<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Tests\Storage;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use PHPUnit\Framework\Attributes\Test;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Storage\DbalStorage;
use RunOpenCode\Component\Intent\Tests\AbstractIntentStorageTestBase;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class DbalStorageTest extends AbstractIntentStorageTestBase
{
    #[Test]
    public function fetch_with_invalid_identifier_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getIntentStorage()->fetch('foo');
    }

    #[Test]
    public function invalidate_with_invalid_identifier_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getIntentStorage()->invalidate('foo');
    }

    protected function getIntentStorage(): IntentStorageInterface
    {
        // Doctrine bundle registers this type within a Symfony application.
        if (!Type::hasType(UlidType::NAME)) {
            Type::addType(UlidType::NAME, UlidType::class);
        }

        $tableName     = 'runopencode_intent';
        $connection    = DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ]);
        $schema        = new Schema();
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $entityManager->method('getConnection')->willReturn($connection);

        $storage = new DbalStorage($connection, $tableName);

        $storage->postGenerateSchema(new GenerateSchemaEventArgs($entityManager, $schema));

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }

        return $storage;
    }
}
