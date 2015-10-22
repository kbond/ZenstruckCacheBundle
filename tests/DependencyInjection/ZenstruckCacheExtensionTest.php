<?php

namespace Zenstruck\RedirectBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Zenstruck\CacheBundle\DependencyInjection\ZenstruckCacheExtension;
use Zenstruck\CacheBundle\Http\Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtensionTest extends AbstractExtensionTestCase
{
    public function testClientAsService()
    {
        $this->load(array('client' => 'foo'));
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasAlias('zenstruck_cache.client', 'foo');
    }

    public function testClientAsClass()
    {
        $this->load(array('client' => 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidClient'));
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasService('zenstruck_cache.client', 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidClient');
    }

    /**
     * @dataProvider invalidClientClassProvider
     */
    public function testInvalidClientClass($class, $expectedExceptionMessage)
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $expectedExceptionMessage);

        $this->load(array('client' => $class));
        $this->compile();
    }

    public function testWithSitemapProviderHosts()
    {
        $this->load(array(
            'client'           => 'foo',
            'sitemap_provider' => array('hosts' => array('http://www.example.com')),
        ));
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.sitemap_provider');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testWithEmptySitemapProviderHosts()
    {
        $this->load(array(
            'client'           => 'foo',
            'sitemap_provider' => array('hosts' => array()),
        ));
        $this->compile();
    }

    public function invalidClientClassProvider()
    {
        return array(
            array(
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidClient1',
                'Client class must implement "Zenstruck\CacheBundle\Http\Client".',
            ),
            array(
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidClient2',
                'Client class must not have required constructor arguments.',
            ),
            array(
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidClient3',
                'Client class must not be abstract.',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(new ZenstruckCacheExtension());
    }
}

class FixtureInvalidClient1
{
}

class FixtureInvalidClient2 implements Client
{
    public function __construct($dependency)
    {
    }

    public function fetch($url, $followRedirects = false, $timeout = 10)
    {
    }
}

abstract class FixtureInvalidClient3 implements Client
{
}

class FixtureValidClient implements Client
{
    public function fetch($url, $followRedirects = false, $timeout = 10)
    {
    }
}
