<?php

namespace Zenstruck\RedirectBundle\Tests\DependencyInjection;

use Http\Adapter\HttpAdapter;
use Http\Message\MessageFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Psr\Http\Message\RequestInterface;
use Zenstruck\CacheBundle\DependencyInjection\ZenstruckCacheExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtensionTest extends AbstractExtensionTestCase
{
    public function testAdapterAndFactoryAsService()
    {
        $this->load(['http_adapter' => 'foo', 'message_factory' => 'bar']);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasAlias('zenstruck_cache.http_adapter', 'foo');
        $this->assertContainerBuilderHasAlias('zenstruck_cache.message_factory', 'bar');
    }

    public function testAdapterAndFactoryAsClass()
    {
        $this->load([
            'http_adapter'    => 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidHttpAdapter',
            'message_factory' => 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidMessageFactory',
        ]);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasService('zenstruck_cache.http_adapter', 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidHttpAdapter');
        $this->assertContainerBuilderHasService('zenstruck_cache.message_factory', 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidMessageFactory');
    }

    /**
     * @dataProvider invalidHttpAdapterClassProvider
     */
    public function testInvalidHttpAdapterClass($class, $expectedExceptionMessage)
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $expectedExceptionMessage);

        $this->load(['http_adapter' => $class, 'message_factory' => 'foo']);
        $this->compile();
    }

    /**
     * @dataProvider invalidMessageFactoryClassProvider
     */
    public function testInvalidMessageFactoryClass($class, $expectedExceptionMessage)
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $expectedExceptionMessage);

        $this->load(['http_adapter' => 'foo', 'message_factory' => $class]);
        $this->compile();
    }

    public function testWithSitemapProviderHosts()
    {
        $this->load([
            'http_adapter'     => 'foo',
            'message_factory'  => 'bar',
            'sitemap_provider' => ['hosts' => ['http://www.example.com']],
        ]);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.sitemap_provider');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testWithEmptySitemapProviderHosts()
    {
        $this->load([
            'http_adapter'     => 'foo',
            'message_factory'  => 'bar',
            'sitemap_provider' => ['hosts' => []],
        ]);
        $this->compile();
    }

    public function invalidHttpAdapterClassProvider()
    {
        return [
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpAdapter1',
                'HttpAdapter class must implement "Http\Adapter\HttpAdapter".',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpAdapter2',
                'HttpAdapter class must not have required constructor arguments.',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpAdapter3',
                'HttpAdapter class must not be abstract.',
            ],
        ];
    }

    public function invalidMessageFactoryClassProvider()
    {
        return [
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidMessageFactory1',
                'MessageFactory class must implement "Http\Message\MessageFactory".',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidMessageFactory2',
                'MessageFactory class must not have required constructor arguments.',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidMessageFactory3',
                'MessageFactory class must not be abstract.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [new ZenstruckCacheExtension()];
    }
}

class FixtureValidHttpAdapter implements HttpAdapter
{
    public function sendRequest(RequestInterface $request, array $options = [])
    {
    }

    public function sendRequests(array $requests, array $options = [])
    {
    }

    public function getName()
    {
    }
}

class FixtureInvalidHttpAdapter1
{
}

class FixtureInvalidHttpAdapter2 implements HttpAdapter
{
    public function __construct($dependency)
    {
    }

    public function sendRequest(RequestInterface $request, array $options = [])
    {
    }

    public function sendRequests(array $requests, array $options = [])
    {
    }

    public function getName()
    {
    }
}

abstract class FixtureInvalidHttpAdapter3 implements HttpAdapter
{
}

class FixtureValidMessageFactory implements MessageFactory
{
    public function createRequest(
        $method,
        $uri,
        $protocolVersion = '1.1',
        array $headers = [],
        $body = null
    ) {
    }

    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        $protocolVersion = '1.1',
        array $headers = [],
        $body = null
    ) {
    }
}

class FixtureInvalidMessageFactory1
{
}

class FixtureInvalidMessageFactory2 implements MessageFactory
{
    public function __construct($dependency)
    {
    }

    public function createRequest(
        $method,
        $uri,
        $protocolVersion = '1.1',
        array $headers = [],
        $body = null
    ) {
    }

    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        $protocolVersion = '1.1',
        array $headers = [],
        $body = null
    ) {
    }
}

abstract class FixtureInvalidMessageFactory3 implements MessageFactory
{
}
