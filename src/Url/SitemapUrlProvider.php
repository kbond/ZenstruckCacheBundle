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
    private $hosts;
    private $httpAdapter;
    private $messageFactory;
    private $urls;

    /**
     * @param array          $hosts
     * @param HttpAdapter    $httpAdapter
     * @param MessageFactory $messageFactory
     */
    public function __construct(array $hosts, HttpAdapter $httpAdapter, MessageFactory $messageFactory)
    {
        if (!class_exists('Symfony\\Component\\DomCrawler\\Crawler') || !class_exists('Symfony\\Component\\CssSelector\\CssSelector')) {
            throw new \RuntimeException('symfony/dom-crawler and symfony/css-selector must be installed to use SitemapUrlProvider.');
        }

        $this->hosts          = $hosts;
        $this->httpAdapter    = $httpAdapter;
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

        $urls = array();

        foreach ($this->hosts as $host) {
            $urls = array_merge($urls, $this->getUrlsForHost($host));
        }

        return $this->urls = $urls;
    }

    /**
     * @param string $host
     *
     * @return array
     */
    private function getUrlsForHost($host)
    {
        $sitemaps = $this->getSitemapEntries($this->addPathToHost('sitemap_index.xml', $host));

        if (empty($sitemaps)) {
            // no index, try sitemap.xml
            return $this->getSitemapEntries($this->addPathToHost('sitemap.xml', $host));
        }

        $urls = array();

        foreach ($sitemaps as $sitemap) {
            $urls = array_merge($urls, $this->getSitemapEntries($sitemap));
        }

        return $urls;
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

    /**
     * @param string $url
     *
     * @return array
     */
    private function getSitemapEntries($url)
    {
        $response = $this->httpAdapter->sendRequest($this->messageFactory->createRequest('GET', $url));

        if (200 !== $response->getStatusCode()) {
            return array();
        }

        $body    = (string) $response->getBody();
        $crawler = new DomCrawler($body);
        $urls    = array();
        $filter  = 'loc';

        // check for namespaces
        if (preg_match('/xmlns:/', $body)) {
            $filter = 'default|loc';
        }

        foreach ($crawler->filter($filter) as $node) {
            $urls[] = $node->nodeValue;
        }

        return $urls;
    }
}
