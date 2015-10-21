<?php

namespace Zenstruck\CacheBundle\Tests\Http;

use Zenstruck\CacheBundle\Http\Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ClientTest extends \PHPUnit_Framework_TestCase
{
    const EXAMPLE_URL = 'http://example.com';
    const MULTI_COUNT = 6;

    /**
     * @dataProvider responseProvider
     */
    public function testFetch($status, $content)
    {
        $response = $this->createClientForFetch($status, $content)->fetch(static::EXAMPLE_URL);

        $this->assertInstanceOf('Zenstruck\CacheBundle\Http\Response', $response);
        $this->assertSame(static::EXAMPLE_URL, $response->getUrl());
        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }

    public function testFetchTimout()
    {
        $response = $this->createClientForFetchTimeout()->fetch(static::EXAMPLE_URL, false, 1);

        $this->assertInstanceOf('Zenstruck\CacheBundle\Http\Response', $response);
        $this->assertSame(static::EXAMPLE_URL, $response->getUrl());
        $this->assertSame(408, $response->getStatusCode());
        $this->assertSame('timeout...', $response->getContent());
    }

    public function testFetchMulti()
    {
        $responses = $this->createClientForFetchMulti()->fetchMulti(array_fill(0, static::MULTI_COUNT, static::EXAMPLE_URL));

        $this->assertInstanceOf('Zenstruck\CacheBundle\Http\Response', $responses[0]);
        $this->assertCount(static::MULTI_COUNT, $responses);
    }

    public function responseProvider()
    {
        return array(
            array(200, '<p>foo</p>'),
            array(200, '<p>foo</p>'),
            array(404, '<p>foo</p>'),
            array(500, '<p>foo</p>'),
            array(301, ''),
        );
    }

    /**
     * @return Client
     */
    abstract protected function createClientForFetch($status, $content);

    /**
     * @return Client
     */
    abstract protected function createClientForFetchTimeout();

    /**
     * @return Client
     */
    abstract protected function createClientForFetchMulti();
}
