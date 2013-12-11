<?php

namespace Zenstruck\Bundle\CacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class UrlProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zenstruck_cache.url_registry')) {
            return;
        }

        $definition = $container->getDefinition(
            'zenstruck_cache.url_registry'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'zenstruck_cache.url_provider'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}
