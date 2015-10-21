<?php

namespace Tests;

use Zenstruck\CacheBundle\ZenstruckCacheBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPassesAreRegistered()
    {
        $container = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('addCompilerPass'))
            ->getMock();

        $container
            ->expects($this->atLeastOnce())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface'));

        $bundle = new ZenstruckCacheBundle();
        $bundle->build($container);
    }
}
