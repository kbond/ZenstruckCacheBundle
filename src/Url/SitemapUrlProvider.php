<?php

namespace Zenstruck\CacheBundle\Url;

use Http\Adapter\HttpAdapter;
use Http\Message\MessageFactory;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SitemapUrlProvider implements UrlProvider
{
    private $sitemaps;
    private $httpAdapter;
    private $messageFactory;
    private $urls;

    /**
     * @param array          $sitemaps
     * @param HttpAdapter    $httpAdapter
     * @param MessageFactory $messageFactory
     */
    public function __construct(array $sitemaps, HttpAdapter $httpAdapter, MessageFactory $messageFactory)
    {
        if (!class_exists('Symfony\\Component\\DomCrawler\\Crawler')) {
            throw new \RuntimeException('symfony/dom-crawler and symfony/css-selector must be installed to use SitemapUrlProvider.');
        }

        $this->sitemaps = $sitemaps;
        $this->httpAdapter = $httpAdapter;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getUrls());
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls()
    {
        if (null !== $this->urls) {
            return $this->urls;
        }

        $urls = [];

        foreach ($this->sitemaps as $sitemap) {
            $urls = array_merge($urls, $this->getUrlsForSitemapUrl($sitemap));
        }

        return $this->urls = $urls;
    }

    /**
     * @param string $sitemap
     *
     * @return array
     */
    private function getUrlsForSitemapUrl($sitemap)
    {
        $path = parse_url($sitemap, PHP_URL_PATH);

        if (null === $path || '/' === trim($path)) {
            return $this->tryDefaultSitemapUrls($sitemap);
        }

        return $this->parseUrl($sitemap);
    }

    /**
     * @param string $host
     *
     * @return array
     */
    private function tryDefaultSitemapUrls($host)
    {
        // try default sitemap_index.xml
        $urls = $this->parseUrl($this->addPathToHost('sitemap_index.xml', $host));

        if (empty($urls)) {
            // try default sitemap.xml
            $urls = $this->parseUrl($this->addPathToHost('sitemap.xml', $host));
        }

        return $urls;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function parseUrl($url)
    {
        $response = $this->httpAdapter->sendRequest($this->messageFactory->createRequest('GET', $url));

        if (200 !== $response->getStatusCode()) {
            return [];
        }

        $body = (string) $response->getBody();

        if (false !== strpos($body, '<sitemapindex')) {
            return $this->parseSitemapIndex($body);
        }

        return $this->getLocEntries($body);
    }

    /**
     * @param string $body
     *
     * @return array
     */
    private function parseSitemapIndex($body)
    {
        $urls = [];

        foreach ($this->getLocEntries($body) as $entry) {
            $urls = array_merge($urls, $this->getUrlsForSitemapUrl($entry));
        }

        return $urls;
    }

    /**
     * @param string $body
     *
     * @return array
     */
    private function getLocEntries($body)
    {
        $crawler = new DomCrawler($body);
        $entries = [];
        $filter = 'loc';

        // check for namespaces
        if (preg_match('/xmlns:/', $body)) {
            $filter = 'default|loc';
        }

        foreach ($crawler->filter($filter) as $node) {
            $entries[] = $node->nodeValue;
        }

        return $entries;
    }

    /**
     * @param string $path
     * @param string $host
     *
     * @return string
     */
    private function addPathToHost($path, $host)
    {
        return sprintf('%s/%s', trim($host, '/'), ltrim($path, '/'));
    }
}
