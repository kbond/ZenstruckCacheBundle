<?php

namespace Zenstruck\CacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class UrlProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zenstruck_cache.crawler')) {
            return;
        }

        $definition     = $container->getDefinition('zenstruck_cache.crawler');
        $taggedServices = $container->findTaggedServiceIds('zenstruck_cache.url_provider');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addUrlProvider', [new Reference($id)]);
        }
    }
}
