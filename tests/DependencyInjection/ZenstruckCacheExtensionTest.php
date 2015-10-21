<?php

namespace Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Zenstruck\CacheBundle\DependencyInjection\ZenstruckCacheExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtensionTest extends AbstractExtensionTestCase
{
    public function testDefault()
    {
        $this->load();
        $this->compile();

        $this->assertTrue($this->container->has('zenstruck_cache.crawler'));
        $this->assertTrue($this->container->has('zenstruck_cache.http_cache_warmup_command'));
        $this->assertFalse($this->container->has('zenstruck_cache.sitemap_provider'));
    }

    public function testWithHosts()
    {
        $this->load(
            array(
                'sitemap_provider' => array(
                    'hosts' => array('http://www.example.com'),
                ),
            )
        );
        $this->compile();

        $this->assertTrue($this->container->has('zenstruck_cache.crawler'));
        $this->assertTrue($this->container->has('zenstruck_cache.http_cache_warmup_command'));
        $this->assertTrue($this->container->has('zenstruck_cache.sitemap_provider'));
    }

    public function testWithEmptyHosts()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->load(
            array(
                'sitemap_provider' => array(
                    'hosts' => array(),
                ),
            )
        );
        $this->compile();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(new ZenstruckCacheExtension());
    }
}
