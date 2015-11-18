<?php

namespace Zenstruck\CacheBundle\DependencyInjection;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\NotFoundException;
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
     * @param string|null      $httpClient
     * @param ContainerBuilder $container
     */
    private function configureHttpClient($httpClient, ContainerBuilder $container)
    {
        $httpClient = $httpClient ?: $this->autoDiscoverHttpClient();

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
     * @param string|null      $messageFactory
     * @param ContainerBuilder $container
     */
    private function configureMessageFactory($messageFactory, ContainerBuilder $container)
    {
        $messageFactory = $messageFactory ?: $this->autoDiscoverMessageFactory();

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

    /**
     * @return string
     */
    private function autoDiscoverHttpClient()
    {
        try {
            return get_class(HttpClientDiscovery::find());
        } catch (NotFoundException $e) {
            throw new InvalidConfigurationException('A HttpClient was not found, please define one in your configuration.', 0, $e);
        }
    }

    /**
     * @return string
     */
    private function autoDiscoverMessageFactory()
    {
        try {
            return get_class(MessageFactoryDiscovery::find());
        } catch (NotFoundException $e) {
            throw new InvalidConfigurationException('A MessageFactory was not found, please define one in your configuration.', 0, $e);
        }
    }
}
