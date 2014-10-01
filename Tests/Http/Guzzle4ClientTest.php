<?php

namespace Zenstruck\CacheBundle\Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Zenstruck\CacheBundle\Http\Guzzle4Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Guzzle4ClientTest extends ClientTest
{
    protected function setUp()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            $this->markTestSkipped('Skipped on PHP 5.3');
        }
    }

    protected function createClientForFetch($status, $content)
    {
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(
            new Mock(
                array(
                    new Response($status, array(), Stream::factory($content))
                )
            )
        );

        return new Guzzle4Client($guzzle);
    }

    protected function createClientForFetchTimeout()
    {
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(
            new Mock(
                array(
                    new RequestException('timeout...', $guzzle->createRequest('GET', self::EXAMPLE_URL))
                )
            )
        );

        return new Guzzle4Client($guzzle);
    }

    protected function createClientForFetchMulti()
    {
        $guzzle = new Client();
        $mocks = array_map(
            function (array $value) {
                return new Response($value[0], array(), Stream::factory($value[1]));
            },
            $this->responseProvider()
        );

        $mocks[] = new RequestException('timeout...', $guzzle->createRequest('GET', self::EXAMPLE_URL));

        $guzzle->getEmitter()->attach(new Mock($mocks));

        return new Guzzle4Client($guzzle);
    }
}
