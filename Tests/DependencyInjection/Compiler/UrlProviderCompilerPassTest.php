<?php

namespace Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zenstruck\CacheBundle\DependencyInjection\Compiler\UrlProviderCompilerPass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class UrlProviderCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $this->setDefinition('zenstruck_cache.crawler', new Definition());

        $provider = new Definition();
        $provider->addTag('zenstruck_cache.url_provider');

        $this->setDefinition('my_provider', $provider);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'zenstruck_cache.crawler',
            'addUrlProvider',
            array(
                new Reference('my_provider')
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new UrlProviderCompilerPass());
    }
}
