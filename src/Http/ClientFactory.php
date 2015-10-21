<?php

namespace Zenstruck\CacheBundle\Http;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ClientFactory
{
    /**
     * @return Client
     */
    public static function create()
    {
        $class = self::getBestClass();

        return new $class();
    }

    /**
     * @return string
     */
    public static function getBestClass()
    {
        if (class_exists('GuzzleHttp\\Client')) {
            return 'Zenstruck\CacheBundle\Http\Guzzle4Client';
        }

        if (class_exists('Guzzle\\Http\\Client')) {
            return 'Zenstruck\CacheBundle\Http\Guzzle3Client';
        }

        throw new \RuntimeException('Cannot find a client.');
    }
}
