<?php

namespace Zenstruck\CacheBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\CacheBundle\DependencyInjection\Compiler\UrlProviderCompilerPass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckCacheBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UrlProviderCompilerPass());
    }

}
