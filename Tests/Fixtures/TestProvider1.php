<?php

namespace Zenstruck\Bundle\CacheBundle\Tests\Fixtures;

use Zenstruck\Bundle\CacheBundle\HttpCache\Provider\UrlProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TestProvider1 implements UrlProviderInterface
{
    public function getUrls($host = null)
    {
        return array(
            'http://www.google.com/',
            'http://www.symfony.com/',
            'http://www.amazon.com/',
            'http://www.knpbundles.com/',
        );
    }
}
