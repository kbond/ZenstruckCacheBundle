<?php

namespace Zenstruck\Bundle\CacheBundle\Tests\HttpCache\Provider;

use Buzz\Browser;
use Zenstruck\Bundle\CacheBundle\HttpCache\Provider\SitemapUrlProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SitemapWarmupProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testNoResults()
    {
        $provider = new SitemapUrlProvider(new Browser());
        $urls = $provider->getUrls('http://example.com');

        $this->assertEmpty($urls);
    }

    public function testWithIndex()
    {
        $provider = new SitemapUrlProvider(new Browser());
        $urls = $provider->getUrls('http://kbond.github.com/ZenstruckCacheBundle');

        $this->assertCount(4, $urls);
        $this->assertTrue(in_array('http://zenstruck.com/', $urls));
    }

    public function testWithoutIndex()
    {
        $provider = new SitemapUrlProvider(new Browser());
        $urls = $provider->getUrls('http://kbond.github.com/ZenstruckCacheBundle/single_sitemap');

        $this->assertCount(2, $urls);
        $this->assertTrue(in_array('http://www.reddit.com/', $urls));
    }
}