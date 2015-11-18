<?php

namespace Zenstruck\RedirectBundle\Tests\DependencyInjection;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Psr\Http\Message\RequestInterface;
use Zenstruck\CacheBundle\DependencyInjection\ZenstruckCacheExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheExtensionTest extends AbstractExtensionTestCase
{
    public function testAutoDiscoverHttpClient()
    {
        $this->load(['message_factory' => 'bar']);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.http_client', 'Http\Adapter\Guzzle5HttpAdapter');
    }

    public function testAutoDiscoverMessageFactory()
    {
        $this->load(['http_client' => 'foo']);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.message_factory', 'Http\Discovery\MessageFactory\GuzzleFactory');
    }

    public function testClientAndFactoryAsService()
    {
        $this->load(['http_client' => 'foo', 'message_factory' => 'bar']);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasAlias('zenstruck_cache.http_client', 'foo');
        $this->assertContainerBuilderHasAlias('zenstruck_cache.message_factory', 'bar');
    }

    public function testClientAndFactoryAsClass()
    {
        $this->load([
            'http_client' => 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidHttpClient',
            'message_factory' => 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidMessageFactory',
        ]);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.crawler');
        $this->assertContainerBuilderNotHasService('zenstruck_cache.sitemap_provider');
        $this->assertContainerBuilderHasService('zenstruck_cache.http_client', 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidHttpClient');
        $this->assertContainerBuilderHasService('zenstruck_cache.message_factory', 'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureValidMessageFactory');
    }

    /**
     * @dataProvider invalidHttpClientClassProvider
     */
    public function testInvalidHttpClientClass($class, $expectedExceptionMessage)
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $expectedExceptionMessage);

        $this->load(['http_client' => $class, 'message_factory' => 'foo']);
        $this->compile();
    }

    /**
     * @dataProvider invalidMessageFactoryClassProvider
     */
    public function testInvalidMessageFactoryClass($class, $expectedExceptionMessage)
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $expectedExceptionMessage);

        $this->load(['http_client' => 'foo', 'message_factory' => $class]);
        $this->compile();
    }

    public function testWithSitemapProviderSitemaps()
    {
        $this->load([
            'http_client' => 'foo',
            'message_factory' => 'bar',
            'sitemap_provider' => ['sitemaps' => ['http://www.example.com']],
        ]);
        $this->compile();

        $this->assertContainerBuilderHasService('zenstruck_cache.sitemap_provider');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testWithEmptySitemapProviderSitemaps()
    {
        $this->load([
            'http_client' => 'foo',
            'message_factory' => 'bar',
            'sitemap_provider' => ['sitemaps' => []],
        ]);
        $this->compile();
    }

    public function invalidHttpClientClassProvider()
    {
        return [
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpClient1',
                'HttpClient class must implement "Http\Client\HttpClient".',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpClient2',
                'HttpClient class must not have required constructor arguments.',
            ],
            [
                'Zenstruck\RedirectBundle\Tests\DependencyInjection\FixtureInvalidHttpClient3',
                'HttpClient class must not be abstract.',
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

class FixtureValidHttpClient implements HttpClient
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

class FixtureInvalidHttpClient1
{
}

class FixtureInvalidHttpClient2 implements HttpClient
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

abstract class FixtureInvalidHttpClient3 implements HttpClient
{
}

class FixtureValidMessageFactory implements MessageFactory
{
    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
    }

    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
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
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
    }

    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
    }
}

abstract class FixtureInvalidMessageFactory3 implements MessageFactory
{
}
