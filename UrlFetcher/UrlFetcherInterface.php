<?php

namespace Zenstruck\Bundle\CacheBundle\UrlFetcher;

use Zenstruck\Bundle\CacheBundle\Exception\UrlFetcherException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface UrlFetcherInterface
{
    /**
     * @param $url
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws UrlFetcherException
     */
    public function fetch($url);

    /**
     * @param $seconds
     *
     * @return null
     */
    public function setTimeout($seconds);
}