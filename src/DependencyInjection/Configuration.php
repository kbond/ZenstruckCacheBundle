<?php

namespace Zenstruck\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('zenstruck_cache');

        $rootNode
            ->children()
                ->scalarNode('client')
                    ->info('Either a class or a service that implements Zenstruck\CacheBundle\Http\Client.')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                ->arrayNode('sitemap_provider')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('hosts')
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
