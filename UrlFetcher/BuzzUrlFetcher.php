<?php

namespace Zenstruck\Bundle\CacheBundle\UrlFetcher;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Bundle\CacheBundle\Exception\UrlFetcherException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class BuzzUrlFetcher implements UrlFetcherInterface
{
    protected $buzz;

    public function __construct(Browser $buzz = null)
    {
        if (!$buzz) {
            $buzz = new Browser(new Curl());
        }

        $this->buzz = $buzz;
    }

    public function fetch($url)
    {
        try {
            /** @var \Buzz\Message\Response $response */
            $response = $this->buzz->get($url);
        } catch (ClientException $e) {
            throw new UrlFetcherException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response($response->getContent(), $response->getStatusCode());
    }

    public function setTimeout($seconds)
    {
        $this->buzz->getClient()->setTimeout($seconds);
    }
}