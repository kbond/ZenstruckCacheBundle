<?php

namespace Zenstruck\CacheBundle;

use Zenstruck\CacheBundle\Http\Client;
use Zenstruck\CacheBundle\UrlProvider\UrlProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Crawler implements \Countable
{
    private $client;
    private $urlProviders;

    /**
     * @param Client        $client
     * @param UrlProvider[] $urlProviders
     */
    public function __construct(Client $client, array $urlProviders = array())
    {
        $this->client = $client;
        $this->urlProviders = $urlProviders;
    }

    /**
     * @param UrlProvider $provider
     */
    public function addUrlProvider(UrlProvider $provider)
    {
        $this->urlProviders[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = 0;

        foreach ($this->urlProviders as $provider) {
            $count += $provider->count();
        }

        return $count;
    }

    /**
     * @param int      $parallelRequests
     * @param bool     $followRedirects
     * @param int      $timeout
     * @param callable $callback         Has a \Zenstruck\CacheBundle\Http\Response argument
     */
    public function crawl($parallelRequests = 10, $followRedirects = false, $timeout = 10, $callback = null)
    {
        if (null !== $callback && !is_callable($callback)) {
            throw new \InvalidArgumentException('Valid callback required.');
        }

        foreach (array_chunk($this->getUrls(), $parallelRequests) as $urls) {
            $responses = $this->client->fetchMulti($urls, $followRedirects, $timeout);

            if ($callback) {
                foreach ($responses as $response) {
                    $callback($response);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getUrls()
    {
        $urls = array();

        foreach ($this->urlProviders as $provider) {
            $urls = array_merge($urls, $provider->getUrls());
        }

        return $urls;
    }
}
