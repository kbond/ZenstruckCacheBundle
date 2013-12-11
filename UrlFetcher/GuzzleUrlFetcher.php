<?php

namespace Zenstruck\Bundle\CacheBundle\UrlFetcher;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class GuzzleUrlFetcher implements UrlFetcherInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->getConfig()->setPath('request.options/exceptions', false);
    }

    public function fetch($url)
    {
        $response = $this->client->get($url)->send();

        return new Response($response->getBody(true), $response->getStatusCode());
    }

    public function setTimeout($seconds)
    {
        $this->client->getConfig()->setPath('request.options/timeout', $seconds);
    }

}
