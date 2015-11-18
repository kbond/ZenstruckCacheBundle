<?php

namespace Zenstruck\CacheBundle\Tests\Url;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use Zenstruck\CacheBundle\Tests\TestCase;
use Zenstruck\CacheBundle\Url\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CrawlerTest extends TestCase
{
    public function testCount()
    {
        $provider1 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider1
            ->expects($this->once())
            ->method('count')
            ->willReturn(3);

        $provider2 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider2
            ->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $crawler = new Crawler($this->mockHttpAdapter(), $this->mockMessageFactory(), null, [$provider1]);
        $crawler->addUrlProvider($provider2);

        $this->assertSame(5, $crawler->count());
    }

    public function testCrawl()
    {
        $request = $this->mockRequest();

        $messageFactory = $this->mockMessageFactory();
        $messageFactory
            ->expects($this->at(0))
            ->method('createRequest')
            ->with('GET', 'foo.com')
            ->willReturn($request);

        $messageFactory
            ->expects($this->at(1))
            ->method('createRequest')
            ->with('GET', 'bar.com')
            ->willReturn($request);

        $messageFactory
            ->expects($this->at(2))
            ->method('createRequest')
            ->with('GET', 'baz.com')
            ->willReturn($request);

        $httpAdapter = $this->mockHttpAdapter();
        $httpAdapter
            ->expects($this->at(0))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 200));

        $httpAdapter
            ->expects($this->at(1))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 200));

        $httpAdapter
            ->expects($this->at(2))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 404));

        $provider1 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider1
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(['foo.com', 'bar.com']);

        $provider2 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider2
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(['baz.com']);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                [LogLevel::DEBUG, '[200] foo.com'],
                [LogLevel::DEBUG, '[200] bar.com'],
                [LogLevel::NOTICE, '[404] baz.com']
            );

        $crawler = new Crawler($httpAdapter, $messageFactory, $logger, [$provider1, $provider2]);

        $urls = [];
        $codes = [];
        $callback = function (ResponseInterface $response, $url) use (&$urls, &$codes) {
            $urls[] = $url;
            $codes[] = $response->getStatusCode();
        };

        $crawler->crawl($callback);

        $this->assertSame(['foo.com', 'bar.com', 'baz.com'], $urls);
        $this->assertSame([200, 200, 404], $codes);
    }
}
