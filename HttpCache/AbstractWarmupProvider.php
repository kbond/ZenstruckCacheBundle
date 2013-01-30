<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class AbstractWarmupProvider implements WarmupProviderInterface
{
    protected function addPathToHost($path, $host)
    {
        return trim($host, '/') . '/' . ltrim($path, '/');
    }
}
