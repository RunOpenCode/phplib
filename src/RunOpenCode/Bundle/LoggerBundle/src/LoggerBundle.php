<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\LoggerBundle;

use Psr\Log\LogLevel;
use RunOpenCode\Component\Logger\Contract\LoggerContextInterface;
use RunOpenCode\Component\Logger\Contract\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use RunOpenCode\Component\Logger\Logger;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class LoggerBundle extends AbstractBundle
{
    protected string $extensionAlias = 'runopencode_logger';

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('debug')
                        ->defaultFalse()
                        ->info('Set to "true" if exception should be thrown instead of logged (useful for development purposes).')
                    ->end()
                    ->enumNode('default_log_level')
                        ->defaultValue(LogLevel::CRITICAL)
                        ->values(Logger::getLogLevels())
                        ->info('Set default log level for exceptions.')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     *
     * @param array{
     *     debug: bool,
     *     default_log_level: string
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerForAutoconfiguration(LoggerContextInterface::class)
                ->addTag('runopencode.logger.context_provider');

        $services = $container->services();

        $services
            ->set(Logger::class)
            ->args([
                '$decorated' => service('monolog.logger'),
                '$contextProviders' => tagged_iterator('runopencode.logger.context_provider'),
                '$debug' => $config['debug'],
                '$defaultLevel' => $config['default_log_level'],
            ]);

        $services->alias(LoggerInterface::class, Logger::class);
    }
}
