<?php

namespace Zenstruck\CacheBundle\Http;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Message\Response as GuzzleResponse;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Guzzle3Client implements Client
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
            $response = $this->createRequest($url, $followRedirects, $timeout)->send();
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (CurlException $e) {
            return $this->createCurlResponse($e);
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

        try {
            foreach ($this->guzzleClient->send($requests) as $response) {
                $responses[] = $this->createResponse($response);
            }
        } catch (MultiTransferException $results) {
            foreach ($results as $result) {
                if (!$result instanceof CurlException) {
                    continue;
                }

                $responses[] = $this->createCurlResponse($result);
            }

            foreach ($results->getFailedRequests() as $request) {
                if (!$request->getResponse() instanceof GuzzleResponse) {
                    continue;
                }

                $responses[] = $this->createResponse($request->getResponse());
            }

            foreach ($results->getSuccessfulRequests() as $request) {
                $responses[] = $this->createResponse($request->getResponse());
            }
        }

        return $responses;
    }

    /**
     * @param string $url
     * @param bool   $followRedirects
     * @param int    $timeout
     *
     * @return \Guzzle\Http\Message\RequestInterface
     */
    private function createRequest($url, $followRedirects, $timeout)
    {
        return $this->guzzleClient->get(
            $url,
            array(),
            array('allow_redirects' => $followRedirects, 'timeout' => $timeout)
        );
    }

    /**
     * @param GuzzleResponse $guzzleResponse
     *
     * @return Response
     */
    private function createResponse(GuzzleResponse $guzzleResponse)
    {
        return new Response(
            $guzzleResponse->getEffectiveUrl(),
            $guzzleResponse->getBody(true),
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getHeaders()->toArray()
        );
    }

    /**
     * @param CurlException $e
     *
     * @throws CurlException
     *
     * @return Response
     */
    private function createCurlResponse(CurlException $e)
    {
        switch ($e->getErrorNo()) {
            case 28: // timeout

                return new Response($e->getRequest()->getUrl(), $e->getError(), 408);
        }

        throw $e;
    }
}
