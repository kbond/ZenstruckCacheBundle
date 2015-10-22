<?php

namespace Zenstruck\CacheBundle\Url;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Zenstruck\CacheBundle\Http\Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Crawler implements \Countable
{
    private $client;
    private $logger;
    private $urlProviders;

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     * @param UrlProvider[]   $urlProviders
     */
    public function __construct(Client $client, LoggerInterface $logger = null, array $urlProviders = array())
    {
        $this->client       = $client;
        $this->logger       = $logger;
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
            $count += count($provider);
        }

        return $count;
    }

    /**
     * @param bool     $followRedirects
     * @param int      $timeout
     * @param callable $callback        Response as first argument, calling URL as second.
     */
    public function crawl($followRedirects = false, $timeout = 10, $callback = null)
    {
        if (null !== $callback && !is_callable($callback)) {
            throw new \InvalidArgumentException('Valid callback required.');
        }

        foreach ($this->getUrls() as $url) {
            $response = $this->client->fetch($url, $followRedirects, $timeout);

            $this->log($response, $url);

            if ($callback) {
                $callback($response, $url);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string            $url
     */
    private function log(ResponseInterface $response, $url)
    {
        if (null === $this->logger) {
            return;
        }

        $status = $response->getStatusCode();

        $this->logger->log($status == 200 ? LogLevel::DEBUG : LogLevel::NOTICE, sprintf('[%s] %s', $status, $url));
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
