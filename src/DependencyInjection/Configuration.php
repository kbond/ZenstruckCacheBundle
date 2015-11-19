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
        $rootNode = $treeBuilder->root('zenstruck_cache');

        $rootNode
            ->children()
                ->scalarNode('http_adapter')
                    ->info('Either a class or a service that implements Http\Adapter\HttpAdapter. Leave blank to attempt auto discovery.')
                    ->defaultNull()
                ->end()
                ->scalarNode('message_factory')
                    ->info('Either a class or a service that implements Http\Message\MessageFactory. Leave blank to attempt auto discovery.')
                    ->defaultNull()
                ->end()
                ->arrayNode('sitemap_provider')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('sitemaps')
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
