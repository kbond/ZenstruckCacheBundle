<?php

namespace Zenstruck\Bundle\CacheBundle\Tests\HttpCache;

use Zenstruck\Bundle\CacheBundle\HttpCache\WarmupRegistry;
use Zenstruck\Bundle\CacheBundle\Tests\Fixtures\TestProvider1;
use Zenstruck\Bundle\CacheBundle\Tests\Fixtures\TestProvider2;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class WarmupRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUrls()
    {
        $registry = new WarmupRegistry();
        $registry->addProvider(new TestProvider1());
        $registry->addProvider(new TestProvider2());

        $this->assertCount(2, $registry->getProviders());

        $urls = $registry->getUrls('foo');

        $this->assertTrue(is_array($urls));
        $this->assertCount(8, $urls);
        $this->assertContains('http://www.ebay.com/', $urls);
    }
}
