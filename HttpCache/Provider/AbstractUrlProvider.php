<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache\Provider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class AbstractUrlProvider implements UrlProviderInterface
{
    protected function addPathToHost($path, $host)
    {
        return trim($host, '/') . '/' . ltrim($path, '/');
    }
}
