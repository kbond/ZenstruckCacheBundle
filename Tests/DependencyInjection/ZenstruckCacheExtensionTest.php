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

    public function testWithHost()
    {
        $this->load(
            array(
                'sitemap_provider' => array(
                    'host' => 'http://www.example.com',
                ),
            )
        );
        $this->compile();

        $this->assertTrue($this->container->has('zenstruck_cache.crawler'));
        $this->assertTrue($this->container->has('zenstruck_cache.http_cache_warmup_command'));
        $this->assertTrue($this->container->has('zenstruck_cache.sitemap_provider'));
    }

    public function testWithHosts()
    {
        $this->load(
            array(
                'sitemap_provider' => array(
                    'hosts' => ['http://www.example.com'],
                ),
            )
        );
        $this->compile();

        $this->assertTrue($this->container->has('zenstruck_cache.crawler'));
        $this->assertTrue($this->container->has('zenstruck_cache.http_cache_warmup_command'));
        $this->assertTrue($this->container->has('zenstruck_cache.sitemap_provider'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(new ZenstruckCacheExtension());
    }
}
