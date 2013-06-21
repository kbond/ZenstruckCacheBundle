<?php

namespace Zenstruck\Bundle\CacheBundle\UrlFetcher;

use Guzzle\Http\StaticClient as Guzzle;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class GuzzleUrlFetcher implements UrlFetcherInterface
{
    protected $params = array(
        'exceptions' => false
    );

    public function fetch($url)
    {
        $response = Guzzle::get($url, $this->params);

        return new Response($response->getBody(true), $response->getStatusCode());
    }

    public function setTimeout($seconds)
    {
        $this->params['timeout'] = $seconds;
    }

}