<?php

namespace Zenstruck\CacheBundle\Url;

use Http\Adapter\HttpAdapter;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Crawler implements \Countable
{
    private $httpAdapter;
    private $messageFactory;
    private $logger;
    private $urlProviders;

    /**
     * @param HttpAdapter     $httpAdapter
     * @param MessageFactory  $messageFactory
     * @param LoggerInterface $logger
     * @param UrlProvider[]   $urlProviders
     */
    public function __construct(HttpAdapter $httpAdapter, MessageFactory $messageFactory, LoggerInterface $logger = null, array $urlProviders = array())
    {
        $this->httpAdapter    = $httpAdapter;
        $this->messageFactory = $messageFactory;
        $this->logger         = $logger;
        $this->urlProviders   = $urlProviders;
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
     * @param callable $callback Response as first argument, calling URL as second
     */
    public function crawl($callback = null)
    {
        if (null !== $callback && !is_callable($callback)) {
            throw new \InvalidArgumentException('Valid callback required.');
        }

        foreach ($this->getUrls() as $url) {
            $response = $this->httpAdapter->sendRequest($this->messageFactory->createRequest('GET', $url));

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
