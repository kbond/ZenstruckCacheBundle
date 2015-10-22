<?php

namespace Zenstruck\CacheBundle\Tests\Url;

use Zend\Diactoros\Response\HtmlResponse as Response;
use Zenstruck\CacheBundle\Url\SitemapUrlProvider;

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
                ->with($response[0])
                ->willReturn($response[1]);
        }

        $provider = new SitemapUrlProvider(array(''), $client);

        $this->assertCount($urlCount, $provider->getUrls());
        $this->assertSame($urls, $provider->getUrls());
        $this->assertSame($urlCount, $provider->count());
    }

    public function fetchProvider()
    {
        return array(
            array(
                array(
                    array('/sitemap_index.xml', new Response('', 404)),
                    array('/sitemap.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)),
                ),
                2,
                array('http://foo.com', 'http://bar.com'),
            ),
            array(
                array(
                    array('/sitemap_index.xml', new Response('', 404)),
                    array('/sitemap.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)),
                ),
                2,
                array('http://foo.com', 'http://bar.com'),
            ),
            array(
                array(
                    array('/sitemap_index.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap.xml</loc></sitemap></sitemapindex>', 200)),
                    array('http://baz.com/sitemap.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)),
                ),
                2,
                array('http://foo.com', 'http://bar.com'),
            ),
            array(
                array(
                    array('/sitemap_index.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap1.xml</loc></sitemap><sitemap><loc>http://baz.com/sitemap2.xml</loc></sitemap></sitemapindex>', 200)),
                    array('http://baz.com/sitemap1.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)),
                    array('http://baz.com/sitemap2.xml', new Response('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)),
                ),
                4,
                array('http://foo.com', 'http://bar.com', 'http://foo.com', 'http://bar.com'),
            ),
            array(
                array(
                    array('/sitemap_index.xml', new Response('', 404)),
                    array('/sitemap.xml', new Response('', 404)),
                ),
                0,
                array(),
            ),
        );
    }
}
