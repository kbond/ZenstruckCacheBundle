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

        $this->configureHttpClient($mergedConfig['http_client'], $container);
        $this->configureMessageFactory($mergedConfig['message_factory'], $container);

        if ($mergedConfig['sitemap_provider']['enabled']) {
            $container->setParameter('zenstruck_cache.sitemap_provider.sitemaps', $mergedConfig['sitemap_provider']['sitemaps']);
            $loader->load('sitemap_provider.xml');
        }
    }

    /**
     * @param string           $httpClient
     * @param ContainerBuilder $container
     */
    private function configureHttpClient($httpClient, ContainerBuilder $container)
    {
        if (!class_exists($httpClient)) {
            // is a service
            $container->setAlias('zenstruck_cache.http_client', $httpClient);

            return;
        }

        $r = new \ReflectionClass($httpClient);

        if (!$r->implementsInterface('Http\Client\HttpClient')) {
            throw new InvalidConfigurationException('HttpClient class must implement "Http\Client\HttpClient".');
        }

        if ($r->isAbstract()) {
            throw new InvalidConfigurationException('HttpClient class must not be abstract.');
        }

        if (null !== $r->getConstructor() && 0 !== $r->getConstructor()->getNumberOfRequiredParameters()) {
            throw new InvalidConfigurationException('HttpClient class must not have required constructor arguments.');
        }

        $httpClient = new Definition($httpClient);
        $httpClient->setPublic(false);
        $container->setDefinition('zenstruck_cache.http_client', $httpClient);
    }

    /**
     * @param string           $messageFactory
     * @param ContainerBuilder $container
     */
    private function configureMessageFactory($messageFactory, ContainerBuilder $container)
    {
        if (!class_exists($messageFactory)) {
            // is a service
            $container->setAlias('zenstruck_cache.message_factory', $messageFactory);

            return;
        }

        $r = new \ReflectionClass($messageFactory);

        if (!$r->implementsInterface('Http\Message\MessageFactory')) {
            throw new InvalidConfigurationException('MessageFactory class must implement "Http\Message\MessageFactory".');
        }

        if ($r->isAbstract()) {
            throw new InvalidConfigurationException('MessageFactory class must not be abstract.');
        }

        if (null !== $r->getConstructor() && 0 !== $r->getConstructor()->getNumberOfRequiredParameters()) {
            throw new InvalidConfigurationException('MessageFactory class must not have required constructor arguments.');
        }

        $messageFactory = new Definition($messageFactory);
        $messageFactory->setPublic(false);
        $container->setDefinition('zenstruck_cache.message_factory', $messageFactory);
    }
}
