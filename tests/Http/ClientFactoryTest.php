<?php

namespace Zenstruck\CacheBundle\Tests\Http;

use Zenstruck\CacheBundle\Http\ClientFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $client = ClientFactory::create();

        if (class_exists('GuzzleHttp\\Client')) {
            $this->assertInstanceOf('Zenstruck\CacheBundle\Http\Guzzle4Client', $client);
        } else {
            $this->assertInstanceOf('Zenstruck\CacheBundle\Http\Guzzle3Client', $client);
        }
    }
}
