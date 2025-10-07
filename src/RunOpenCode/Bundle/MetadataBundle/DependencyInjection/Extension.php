<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BundleExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class Extension extends BundleExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'runopencode_metadata';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return 'https://www.runopencode.com/xsd-schema/metadata-bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath(): string
    {
        return __DIR__ . '/../Resources/schema';
    }


    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $loader        = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        /**
         * @var array{
         *     cache_pool: string | null
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('runopencode.metadata.cache_pool', $config['cache_pool'] ?? 'cache.system');
        $loader->load('services.xml');
    }
}
