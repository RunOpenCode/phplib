<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\IntentBundle;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use RunOpenCode\Component\Intent\Command\ClearExpiredIntentsCommand;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Storage\CacheStorage;
use RunOpenCode\Component\Intent\Storage\DbalStorage;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @phpstan-type Config = array{
 *     driver: 'dbal'|'cache',
 *     dbal: array{
 *         connection: non-empty-string,
 *         table_name: non-empty-string,
 *     },
 *     cache: array{
 *         pool: non-empty-string,
 *     },
 * }
 */
final class IntentBundle extends AbstractBundle
{
    protected string $extensionAlias = 'runopencode_intent';

    /**
     * {@inheritdoc}
     *
     * Intent identifiers are stored as ULIDs, so the type has to be known to Doctrine.
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$builder->hasExtension('doctrine')) {
            return;
        }

        $container->extension('doctrine', [
            'dbal' => [
                'types' => [
                    UlidType::NAME => UlidType::class,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
            ->children()
            ->enumNode('driver')
            ->values(['dbal', 'cache'])
            ->defaultValue('dbal')
            ->info('Driver used to persist intents.')
            ->end()
            ->arrayNode('dbal')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('connection')
            ->defaultValue(Connection::class)
            ->cannotBeEmpty()
            ->info('Service id of the connection to use. By default, default connection is used.')
            ->end()
            ->scalarNode('table_name')
            ->defaultValue('runopencode_intent')
            ->info('Name of the table within which intents are stored.')
            ->end()
            ->end()
            ->end()
            ->arrayNode('cache')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('pool')
            ->defaultValue('cache.app')
            ->info('Service id of the PSR-6 cache pool within which intents are stored.')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     *
     * @param Config $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        match ($config['driver']) {
            'dbal' => $this->registerDbalDriver($config, $services),
            'cache' => $this->registerCacheDriver($config, $services), //@phpstan-ignore-line match.alwaysTrue
            default => throw new InvalidConfigurationException(\sprintf(
                'Invalid driver "%s" configured for intents. Valid values are: "dbal", "cache".',
                $config['driver']
            )),
        };

        // Maintenance command can not be used without "symfony/console".
        if (!\class_exists(Command::class)) {
            return;
        }

        $services
            ->set(ClearExpiredIntentsCommand::class)
            ->args([
                '$storage' => service(IntentStorageInterface::class),
            ])
            ->tag('console.command');
    }

    /**
     * @param Config $config
     */
    private function registerDbalDriver(array $config, ServicesConfigurator $services): void
    {
        if (!\class_exists(Connection::class)) {
            throw new InvalidConfigurationException(
                'Intents can not be stored within a database table, "doctrine/dbal" is not installed.'
            );
        }

        $connection = $config['dbal']['connection'];

        $storage = $services->set(DbalStorage::class);

        $storage->args([
            '$connection' => service($connection),
            '$tableName'  => $config['dbal']['table_name'],
        ]);

        $services->alias(IntentStorageInterface::class, DbalStorage::class);

        // Schema generation is an ORM feature, so generate the table only when it is available.
        if (!\class_exists(GenerateSchemaEventArgs::class)) {
            return;
        }

        $storage->tag('doctrine.event_listener', [
            'event' => 'postGenerateSchema',
        ]);
    }

    /**
     * @param Config $config
     */
    private function registerCacheDriver(array $config, ServicesConfigurator $services): void
    {
        $services
            ->set(CacheStorage::class)
            ->args([
                '$pool' => service($config['cache']['pool']),
            ]);

        $services->alias(IntentStorageInterface::class, CacheStorage::class);
    }
}
