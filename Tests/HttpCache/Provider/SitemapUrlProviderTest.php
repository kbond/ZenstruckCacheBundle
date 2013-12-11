<?php

namespace Zenstruck\Bundle\CacheBundle\Tests\HttpCache\Provider;

use Zenstruck\Bundle\CacheBundle\HttpCache\Provider\SitemapUrlProvider;
use Zenstruck\Bundle\CacheBundle\UrlFetcher\BuzzUrlFetcher;
use Zenstruck\Bundle\CacheBundle\UrlFetcher\GuzzleUrlFetcher;
use Zenstruck\Bundle\CacheBundle\UrlFetcher\UrlFetcherInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SitemapWarmupProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider urlFetcherProvider
     */
    public function testNoResults(UrlFetcherInterface $fetcher)
    {
        $provider = new SitemapUrlProvider($fetcher);
        $urls = $provider->getUrls('http://example.com');

        $this->assertEmpty($urls);
    }

    /**
     * @dataProvider urlFetcherProvider
     */
    public function testWithIndex(UrlFetcherInterface $fetcher)
    {
        $provider = new SitemapUrlProvider($fetcher);
        $urls = $provider->getUrls('http://kbond.github.io/ZenstruckCacheBundle');

        $this->assertCount(4, $urls);
        $this->assertTrue(in_array('http://zenstruck.com/', $urls));
    }

    /**
     * @dataProvider urlFetcherProvider
     */
    public function testWithoutIndex(UrlFetcherInterface $fetcher)
    {
        $provider = new SitemapUrlProvider($fetcher);
        $urls = $provider->getUrls('http://kbond.github.io/ZenstruckCacheBundle/single_sitemap');

        $this->assertCount(2, $urls);
        $this->assertTrue(in_array('http://www.reddit.com/', $urls));
    }

    public function urlFetcherProvider()
    {
        return array(
            array(new BuzzUrlFetcher()),
            array(new GuzzleUrlFetcher())
        );
    }
}
