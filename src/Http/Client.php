<?php

namespace Zenstruck\CacheBundle\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Client
{
    /**
     * Fetches a response for a single URL.
     *
     * @param string $url
     * @param bool   $followRedirects
     * @param int    $timeout
     *
     * @return ResponseInterface
     */
    public function fetch($url, $followRedirects = false, $timeout = 10);
}
