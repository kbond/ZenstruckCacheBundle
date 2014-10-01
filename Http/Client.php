<?php

namespace Zenstruck\CacheBundle\Http;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Client
{
    /**
     * Fetches a response for a single URL
     *
     * @param string $url
     * @param bool   $followRedirects
     * @param int    $timeout
     *
     * @return Response
     */
    public function fetch($url, $followRedirects = false, $timeout = 10);

    /**
     * Fetches responses from multiple URLs
     *
     * @param array $urls
     * @param bool  $followRedirects
     * @param int   $timeout
     *
     * @return Response[]
     */
    public function fetchMulti(array $urls, $followRedirects = false, $timeout = 10);
}
