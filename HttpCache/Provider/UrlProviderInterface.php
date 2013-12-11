<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache\Provider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface UrlProviderInterface
{
    /**
     * @param string $host
     *
     * @return array The array of urls to warm
     */
    public function getUrls($host = null);
}
