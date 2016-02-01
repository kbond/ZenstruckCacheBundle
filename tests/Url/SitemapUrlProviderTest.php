<?php

namespace Zenstruck\CacheBundle\Tests\Url;

use Zenstruck\CacheBundle\Tests\TestCase;
use Zenstruck\CacheBundle\Url\SitemapUrlProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SitemapUrlProviderTest extends TestCase
{
    /**
     * @dataProvider fetchProvider
     */
    public function testGetUrls(array $sitemaps, array $responses, $urlCount, array $urls)
    {
        $httpClient = $this->mockHttpClient();
        $messageFactory = $this->mockMessageFactory();

        foreach ($responses as $key => $response) {
            $request = $this->mockRequest();

            $messageFactory
                ->expects($this->at($key))
                ->method('createRequest')
                ->with('GET', $response[0])
                ->willReturn($request);

            $httpClient
                ->expects($this->at($key))
                ->method('sendRequest')
                ->with($request)
                ->willReturn($response[1]);
        }

        $provider = new SitemapUrlProvider($sitemaps, $httpClient, $messageFactory);

        $this->assertCount($urlCount, $provider->getUrls());
        $this->assertSame($urls, $provider->getUrls());
        $this->assertSame($urlCount, $provider->count());
    }

    public function fetchProvider()
    {
        return [
            [
                ['http://ex.com'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('', 404)],
                    ['http://ex.com/sitemap.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                2,
                ['http://foo.com', 'http://bar.com'],
            ],
            [
                ['http://ex.com'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('', 404)],
                    ['http://ex.com/sitemap.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                2,
                ['http://foo.com', 'http://bar.com'],
            ],
            [
                ['http://ex.com'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap.xml</loc></sitemap></sitemapindex>', 200)],
                    ['http://baz.com/sitemap.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                2,
                ['http://foo.com', 'http://bar.com'],
            ],
            [
                ['http://ex.com'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap1.xml</loc></sitemap><sitemap><loc>http://baz.com/sitemap2.xml</loc></sitemap></sitemapindex>', 200)],
                    ['http://baz.com/sitemap1.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                    ['http://baz.com/sitemap2.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                4,
                ['http://foo.com', 'http://bar.com', 'http://foo.com', 'http://bar.com'],
            ],
            [
                ['http://ex.com'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('', 404)],
                    ['http://ex.com/sitemap.xml', $this->mockResponse('', 404)],
                ],
                0,
                [],
            ],
            [
                ['http://ex.com/sitemap_index.xml', 'http://ex.com/sitemap.xml'],
                [
                    ['http://ex.com/sitemap_index.xml', $this->mockResponse('', 404)],
                    ['http://ex.com/sitemap.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                2,
                ['http://foo.com', 'http://bar.com'],
            ],
            [
                ['http://ex.com/sitemap_index1.xml'],
                [
                    ['http://ex.com/sitemap_index1.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://ex.com/sitemap_index2.xml</loc></sitemap><sitemap><loc>http://baz.com/sitemap1.xml</loc></sitemap></sitemapindex>', 200)],
                    ['http://ex.com/sitemap_index2.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>http://baz.com/sitemap2.xml</loc></sitemap></sitemapindex>', 200)],
                    ['http://baz.com/sitemap2.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                    ['http://baz.com/sitemap1.xml', $this->mockResponse('<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>http://foo.com</loc></url><url><loc>http://bar.com</loc></url></urlset>', 200)],
                ],
                4,
                ['http://foo.com', 'http://bar.com', 'http://foo.com', 'http://bar.com'],
            ],
        ];
    }
}
