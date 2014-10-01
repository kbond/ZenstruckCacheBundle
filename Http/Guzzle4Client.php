<?php

namespace Zenstruck\CacheBundle\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Guzzle4Client implements Client
{
    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient = null)
    {
        if (null === $guzzleClient) {
            $guzzleClient = new GuzzleClient();
        }

        $this->guzzleClient = $guzzleClient;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($url, $followRedirects = false, $timeout = 10)
    {
        try {
            $response = $this->guzzleClient->send($this->createRequest($url, $followRedirects, $timeout));
        } catch (RequestException $e) {
            return $this->createRequestExceptionResponse($e);
        }

        return $this->createResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchMulti(array $urls, $followRedirects = false, $timeout = 10)
    {
        $urls = array_values($urls);

        if (1 === count($urls)) {
            return array($this->fetch($urls[0], $followRedirects, $timeout));
        }

        $requests = array();
        $responses = array();

        foreach ($urls as $url) {
            $requests[] = $this->createRequest($url, $followRedirects, $timeout);
        }

        $this->guzzleClient->sendAll(
            $requests,
            [
                'complete' => function (CompleteEvent $event) use (&$responses) {
                    $responses[] = $this->createResponse($event->getResponse());
                },
                'error' => function (ErrorEvent $event) use (&$responses) {
                    $responses[] = $this->createRequestExceptionResponse($event->getException());
                }
            ]
        );

        return $responses;
    }

    private function createRequest($url, $followRedirects = false, $timeout = 10)
    {
        return $this->guzzleClient->createRequest(
            'GET',
            $url,
            [
                'timeout' => $timeout,
                'allow_redirects' => $followRedirects
            ]
        );
    }

    /**
     * @param ResponseInterface $guzzleResponse
     *
     * @return Response
     */
    private function createResponse(ResponseInterface $guzzleResponse)
    {
        return new Response(
            $guzzleResponse->getEffectiveUrl(),
            (string)$guzzleResponse->getBody(),
            (int)$guzzleResponse->getStatusCode(),
            $guzzleResponse->getHeaders()
        );
    }

    /**
     * @param RequestException $e
     *
     * @return Response
     */
    private function createRequestExceptionResponse(RequestException $e)
    {
        if (!$e->hasResponse()) {
            $message = $e->getMessage();

            if (preg_match('/timed?[\s-]?out/i', $message)) {
                // curl timeout
                return new Response($e->getRequest()->getUrl(), $message, 408);
            }

            throw $e;
        }

        return $this->createResponse($e->getResponse());
    }
}
