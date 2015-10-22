<?php

namespace Zenstruck\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

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
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureClient($mergedConfig['client'], $container);

        if ($mergedConfig['sitemap_provider']['enabled']) {
            $container->setParameter('zenstruck_cache.sitemap_provider.hosts', $mergedConfig['sitemap_provider']['hosts']);
            $loader->load('sitemap_provider.xml');
        }
    }

    /**
     * @param string           $client
     * @param ContainerBuilder $container
     */
    private function configureClient($client, ContainerBuilder $container)
    {
        if (!class_exists($client)) {
            // is a service
            $container->setAlias('zenstruck_cache.client', $client);

            return;
        }

        $r = new \ReflectionClass($client);

        if (!$r->implementsInterface('Zenstruck\CacheBundle\Http\Client')) {
            throw new InvalidConfigurationException('Client class must implement "Zenstruck\CacheBundle\Http\Client".');
        }

        if ($r->isAbstract()) {
            throw new InvalidConfigurationException('Client class must not be abstract.');
        }

        if (null !== $r->getConstructor() && 0 !== $r->getConstructor()->getNumberOfRequiredParameters()) {
            throw new InvalidConfigurationException('Client class must not have required constructor arguments.');
        }

        $client = new Definition($client);
        $client->setPublic(false);
        $container->setDefinition('zenstruck_cache.client', $client);
    }
}
