<?php

namespace Zenstruck\CacheBundle\UrlProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface UrlProvider extends \Countable
{
    /**
     * @return array
     */
    public function getUrls();
}
