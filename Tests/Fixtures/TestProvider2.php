<?php

namespace Zenstruck\Bundle\CacheBundle\Tests\Fixtures;

use Zenstruck\Bundle\CacheBundle\HttpCache\WarmupProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TestProvider2 implements WarmupProviderInterface
{
    public function getUrls($host = null)
    {
        return array(
            'http://www.google.com/',
            'http://www.ebay.com/',
            'http://www.servergrove.com/',
            'http://www.zenstruck.com/',
        );
    }
}
