<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle;

use RunOpenCode\Component\Metadata\Contract\ReaderInterface;
use RunOpenCode\Component\Metadata\Reader;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class MetadataBundle extends AbstractBundle
{
    protected string $extensionAlias = 'runopencode_metadata';

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        // @phpstan-ignore-next-line
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('cache_pool')
                        ->defaultValue('cache.system')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     *
     * @param array{
     *      cache_pool: string | null
     *  } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services
            ->set(Reader::class)
            ->factory([ReaderFactory::class, 'create'])
            ->args([
                service('runopencode.metadata.cache_pool'),
                param('kernel.environment')
            ]);

        $services
            ->alias(ReaderInterface::class, Reader::class);

        $services
            ->alias('runopencode.metadata.cache_pool', $config['cache_pool'] ?? 'cache.system');
    }
}
