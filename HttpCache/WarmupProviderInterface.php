<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface WarmupProviderInterface
{
    /**
     * @param string $host
     *
     * @return array The array of urls to warm
     */
    public function getUrls($host = null);
}
