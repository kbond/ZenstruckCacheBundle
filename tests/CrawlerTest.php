<?php

namespace Zenstruck\CacheBundle\Tests;

use Zenstruck\CacheBundle\Crawler;
use Zenstruck\CacheBundle\Http\Response;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $provider1 = $this->getMock('Zenstruck\CacheBundle\UrlProvider\UrlProvider');
        $provider1
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(3));

        $provider2 = $this->getMock('Zenstruck\CacheBundle\UrlProvider\UrlProvider');
        $provider2
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(2));

        $crawler = new Crawler($this->getMock('Zenstruck\CacheBundle\Http\Client'), array($provider1));
        $crawler->addUrlProvider($provider2);

        $this->assertSame(5, $crawler->count());
    }

    public function testCrawl()
    {
        $client = $this->getMock('Zenstruck\CacheBundle\Http\Client');
        $client
            ->expects($this->at(0))
            ->method('fetchMulti')
            ->with(array('foo.com', 'bar.com'))
            ->will(
                $this->returnValue(
                    array(
                        new Response('foo.com'),
                        new Response('bar.com'),
                    )
                )
            )
        ;

        $client
            ->expects($this->at(1))
            ->method('fetchMulti')
            ->with(array('baz.com'))
            ->will(
                $this->returnValue(
                    array(
                        new Response('baz.com', '', 404),
                    )
                )
            )
        ;

        $provider1 = $this->getMock('Zenstruck\CacheBundle\UrlProvider\UrlProvider');
        $provider1
            ->expects($this->once())
            ->method('getUrls')
            ->will($this->returnValue(array('foo.com', 'bar.com')));

        $provider2 = $this->getMock('Zenstruck\CacheBundle\UrlProvider\UrlProvider');
        $provider2
            ->expects($this->once())
            ->method('getUrls')
            ->will($this->returnValue(array('baz.com')));

        $crawler = new Crawler($client, array($provider1, $provider2));

        $codes = array();
        $callback = function (Response $response) use (&$codes) {
            $codes[] = $response->getStatusCode();
        };

        $crawler->crawl(2, false, 10, $callback);

        $this->assertSame(array(200, 200, 404), $codes);
    }
}
