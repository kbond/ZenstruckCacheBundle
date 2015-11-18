<?php

namespace Zenstruck\CacheBundle\Tests;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClient
     */
    public function mockHttpClient()
    {
        return $this->getMock('Http\Client\HttpClient');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageFactory
     */
    public function mockMessageFactory()
    {
        return $this->getMock('Http\Message\MessageFactory');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestInterface
     */
    protected function mockRequest()
    {
        return $this->getMock('Psr\Http\Message\RequestInterface');
    }

    /**
     * @param string $body
     * @param int    $statusCode
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    protected function mockResponse($body, $statusCode)
    {
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }
}
