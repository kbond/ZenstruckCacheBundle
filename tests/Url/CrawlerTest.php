<?php

namespace Zenstruck\CacheBundle\Tests\Url;

use Psr\Log\LogLevel;
use Zend\Diactoros\Response\HtmlResponse as Response;
use Zenstruck\CacheBundle\Url\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CrawlerTest extends \PHPUnit_Framework_TestCase
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

        $crawler = new Crawler($this->getMock('Zenstruck\CacheBundle\Http\Client'), null, array($provider1));
        $crawler->addUrlProvider($provider2);

        $this->assertSame(5, $crawler->count());
    }

    public function testCrawl()
    {
        $client = $this->getMock('Zenstruck\CacheBundle\Http\Client');
        $client
            ->expects($this->at(0))
            ->method('fetch')
            ->with('foo.com')
            ->willReturn(new Response(''));

        $client
            ->expects($this->at(1))
            ->method('fetch')
            ->with('bar.com')
            ->willReturn(new Response(''));

        $client
            ->expects($this->at(2))
            ->method('fetch')
            ->with('baz.com')
            ->willReturn(new Response('', 404));

        $provider1 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider1
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(array('foo.com', 'bar.com'));

        $provider2 = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider2
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(array('baz.com'));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                array(LogLevel::DEBUG, '[200] foo.com'),
                array(LogLevel::DEBUG, '[200] bar.com'),
                array(LogLevel::NOTICE, '[404] baz.com')
            );

        $crawler = new Crawler($client, $logger, array($provider1, $provider2));

        $urls     = array();
        $codes    = array();
        $callback = function (Response $response, $url) use (&$urls, &$codes) {
            $urls[]  = $url;
            $codes[] = $response->getStatusCode();
        };

        $crawler->crawl(false, 10, $callback);

        $this->assertSame(array('foo.com', 'bar.com', 'baz.com'), $urls);
        $this->assertSame(array(200, 200, 404), $codes);
    }
}
