<?php

namespace Zenstruck\Bundle\CacheBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zenstruck\Bundle\CacheBundle\DependencyInjection\Compiler\UrlProviderCompilerPass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UrlProviderCompilerPass());
    }
}