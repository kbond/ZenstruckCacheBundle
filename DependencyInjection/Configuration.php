<?php

namespace Zenstruck\Bundle\CacheBundle\DependencyInjection;

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
                ->booleanNode('sitemap_provider')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
