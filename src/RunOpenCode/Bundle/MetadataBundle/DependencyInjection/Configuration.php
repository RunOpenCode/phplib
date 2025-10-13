<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('runopencode_metadata');
        $rootNode    = $treeBuilder->getRootNode();

        /**
         * @phpstan-ignore-next-line
         */
        $rootNode
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('cache_pool')
                        ->defaultValue('cache.system')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
