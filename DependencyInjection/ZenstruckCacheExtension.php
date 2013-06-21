<?php

namespace Zenstruck\Bundle\CacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (class_exists('\Guzzle\Http\Client')) {
            $loader->load('guzzle_url_fetcher.xml');
        } elseif (class_exists('\Buzz\Browser')) {
            $loader->load('buzz_url_fetcher.xml');
        } else {
            throw new \Exception('Either Guzzle or Buzz must be available to use ZenstruckCacheBundle.');
        }

        $loader->load('url_registry.xml');

        if ($config['sitemap_provider']) {
            $loader->load('sitemap_provider.xml');
        }
    }
}
