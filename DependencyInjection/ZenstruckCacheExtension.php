<?php

namespace Zenstruck\CacheBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;
use Zenstruck\CacheBundle\Http\ClientFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $client = new Definition(ClientFactory::getBestClass());
        $client->setPublic(false);

        $container->setDefinition('zenstruck_cache.client', $client);
        $container->getDefinition('zenstruck_cache.crawler')->replaceArgument(0, new Reference('zenstruck_cache.client'));

        if ($mergedConfig['sitemap_provider']['enabled']) {
            $container->setParameter('zenstruck_cache.sitemap_provider.host', $mergedConfig['sitemap_provider']['host']);
            $loader->load('sitemap_provider.xml');
        }
    }
}
