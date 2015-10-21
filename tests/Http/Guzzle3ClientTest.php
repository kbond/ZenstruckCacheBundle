<?php

namespace Zenstruck\CacheBundle\Tests\Http;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Zenstruck\CacheBundle\Http\Guzzle3Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Guzzle3ClientTest extends ClientTest
{
    protected function createClientForFetch($status, $content)
    {
        $guzzle = new Client();
        $guzzle->addSubscriber(new MockPlugin(array(new Response($status, null, $content))));

        return new Guzzle3Client($guzzle);
    }

    protected function createClientForFetchTimeout()
    {
        $e = new CurlException();
        $e->setError('timeout...', 28);
        $guzzle = new Client();
        $guzzle->addSubscriber(new MockPlugin(array($e)));

        return new Guzzle3Client($guzzle);
    }

    protected function createClientForFetchMulti()
    {
        $mocks = array_map(
            function (array $value) {
                return new Response($value[0], null, $value[1]);
            },
            $this->responseProvider()
        );

        $e = new CurlException();
        $e->setError('timeout...', 28);

        $mocks[] = $e;

        $guzzle = new Client();
        $guzzle->addSubscriber(new MockPlugin($mocks));

        return new Guzzle3Client($guzzle);
    }
}
