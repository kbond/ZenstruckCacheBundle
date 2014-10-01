<?php

namespace Zenstruck\CacheBundle\Tests\UrlProvider;

use Zenstruck\CacheBundle\Http\Response;
use Zenstruck\CacheBundle\UrlProvider\SitemapUrlProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SitemapUrlProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider fetchProvider
     */
    public function testGetUrls(array $responses, $urlCount, array $urls)
    {
        $client = $this->getMock('Zenstruck\CacheBundle\Http\Client');

        foreach ($responses as $key => $response) {
            $client
                ->expects($this->at($key))
                ->method('fetch')
                ->with($response->getUrl())
                ->will($this->returnValue($response))
            ;
        }

        $provider = new SitemapUrlProvider('', $client);

        $this->assertCount($urlCount, $provider->getUrls());
        $this->assertSame($urls, $provider->getUrls());
        $this->assertSame($urlCount, $provider->count());
    }

    public function fetchProvider()
    {
        return array(
            array(
                array(
                    new Response('/sitemap_index.xml', '', 404),
                    new Response('/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)
                ),
                2,
                array('http://foo.com', 'http://bar.com')
            ),
            array(
                array(
                    new Response('/sitemap_index.xml', '', 404),
                    new Response('/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)
                ),
                2,
                array('http://foo.com', 'http://bar.com')
            ),
            array(
                array(
                    new Response('/sitemap_index.xml', '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap.xml</loc></sitemap></sitemapindex>', 200),
                    new Response('http://baz.com/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)
                ),
                2,
                array('http://foo.com', 'http://bar.com')
            ),
            array(
                array(
                    new Response('/sitemap_index.xml', '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap1.xml</loc></sitemap><sitemap><loc>http://baz.com/sitemap2.xml</loc></sitemap></sitemapindex>', 200),
                    new Response('http://baz.com/sitemap1.xml', '<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200),
                    new Response('http://baz.com/sitemap2.xml', '<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)
                ),
                4,
                array('http://foo.com', 'http://bar.com', 'http://foo.com', 'http://bar.com')
            ),
            array(
                array(
                    new Response('/sitemap_index.xml', '', 404),
                    new Response('/sitemap.xml', '', 404)
                ),
                0,
                array()
            )
        );
    }
}
