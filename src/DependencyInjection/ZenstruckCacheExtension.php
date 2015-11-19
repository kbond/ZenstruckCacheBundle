<?php

namespace Zenstruck\CacheBundle\DependencyInjection;

use Http\Discovery\HttpAdapterDiscovery;
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

        $this->configureHttpAdapter($mergedConfig['http_adapter'], $container);
        $this->configureMessageFactory($mergedConfig['message_factory'], $container);

        if ($mergedConfig['sitemap_provider']['enabled']) {
            $container->setParameter('zenstruck_cache.sitemap_provider.sitemaps', $mergedConfig['sitemap_provider']['sitemaps']);
            $loader->load('sitemap_provider.xml');
        }
    }

    /**
     * @param string|null      $httpAdapter
     * @param ContainerBuilder $container
     */
    private function configureHttpAdapter($httpAdapter, ContainerBuilder $container)
    {
        $httpAdapter = $httpAdapter ?: $this->autoDiscoverHttpAdapter();

        if (!class_exists($httpAdapter)) {
            // is a service
            $container->setAlias('zenstruck_cache.http_adapter', $httpAdapter);

            return;
        }

        $r = new \ReflectionClass($httpAdapter);

        if (!$r->implementsInterface('Http\Adapter\HttpAdapter')) {
            throw new InvalidConfigurationException('HttpAdapter class must implement "Http\Adapter\HttpAdapter".');
        }

        if ($r->isAbstract()) {
            throw new InvalidConfigurationException('HttpAdapter class must not be abstract.');
        }

        if (null !== $r->getConstructor() && 0 !== $r->getConstructor()->getNumberOfRequiredParameters()) {
            throw new InvalidConfigurationException('HttpAdapter class must not have required constructor arguments.');
        }

        $httpAdapter = new Definition($httpAdapter);
        $httpAdapter->setPublic(false);
        $container->setDefinition('zenstruck_cache.http_adapter', $httpAdapter);
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
    private function autoDiscoverHttpAdapter()
    {
        try {
            return get_class(HttpAdapterDiscovery::find());
        } catch (NotFoundException $e) {
            throw new InvalidConfigurationException('A HttpAdapter was not found, please define one in your configuration.', 0, $e);
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
