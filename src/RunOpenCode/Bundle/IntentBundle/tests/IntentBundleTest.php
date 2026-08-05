<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\IntentBundle\Tests;

use Doctrine\DBAL\Connection;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\Test;
use RunOpenCode\Bundle\IntentBundle\IntentBundle;
use RunOpenCode\Component\Intent\Command\ClearExpiredIntentsCommand;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Storage\CacheStorage;
use RunOpenCode\Component\Intent\Storage\DbalStorage;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

final class IntentBundleTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up minimal required parameters that Symfony kernel normally provides
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.build_dir', 'tmp');
    }

    #[Test]
    public function load_extension_with_default_configuration(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(DbalStorage::class);
        $this->assertContainerBuilderHasAlias(IntentStorageInterface::class, DbalStorage::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(DbalStorage::class, '$connection', new Reference(Connection::class));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(DbalStorage::class, '$tableName', 'runopencode_intent');
    }

    #[Test]
    public function load_extension_with_custom_dbal_configuration(): void
    {
        $this->load([
            'dbal' => [
                'connection' => 'my.connection',
                'table_name' => 'my_intent',
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(DbalStorage::class, '$connection', new Reference('my.connection'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(DbalStorage::class, '$tableName', 'my_intent');
    }

    #[Test]
    public function contributes_storage_table_to_generated_schema(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(DbalStorage::class, 'doctrine.event_listener', [
            'event' => 'postGenerateSchema',
        ]);
    }

    #[Test]
    public function load_extension_with_cache_driver(): void
    {
        $this->load([
            'driver' => 'cache',
        ]);

        $this->assertContainerBuilderHasService(CacheStorage::class);
        $this->assertContainerBuilderHasAlias(IntentStorageInterface::class, CacheStorage::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(CacheStorage::class, '$pool', new Reference('cache.app'));

        $this->assertContainerBuilderNotHasService(DbalStorage::class);
    }

    #[Test]
    public function load_extension_with_custom_cache_configuration(): void
    {
        $this->load([
            'driver' => 'cache',
            'cache'  => [
                'pool' => 'my.pool',
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(CacheStorage::class, '$pool', new Reference('my.pool'));
    }

    #[Test]
    public function registers_maintenance_command(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(ClearExpiredIntentsCommand::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(ClearExpiredIntentsCommand::class, 'console.command');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(ClearExpiredIntentsCommand::class, '$storage', new Reference(IntentStorageInterface::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        $extension = new IntentBundle()->getContainerExtension();

        if (!$extension instanceof ExtensionInterface) {
            throw new \RuntimeException('Failed to get container extension from IntentBundle.');
        }

        return [$extension];
    }
}
