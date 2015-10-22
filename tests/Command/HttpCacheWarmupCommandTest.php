<?php

namespace Zenstruck\CacheBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zend\Diactoros\Response\HtmlResponse as Response;
use Zenstruck\CacheBundle\Command\HttpCacheWarmupCommand;
use Zenstruck\CacheBundle\Url\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $provider = $this->getMock('Zenstruck\CacheBundle\Url\UrlProvider');
        $provider
            ->expects($this->once())
            ->method('count')
            ->willReturn(3);
        $provider
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(array('http://foo.com', 'http://bar.com', 'http://baz.com'));

        $client = $this->getMock('Zenstruck\CacheBundle\Http\Client');
        $client
            ->expects($this->at(0))
            ->method('fetch')
            ->with('http://foo.com')
            ->willReturn(new Response('', 200));
        $client
            ->expects($this->at(1))
            ->method('fetch')
            ->with('http://bar.com')
            ->willReturn(new Response('', 404));
        $client
            ->expects($this->at(2))
            ->method('fetch')
            ->with('http://baz.com')
            ->willReturn(new Response('', 200));

        $crawler = new Crawler($client, null, array($provider));

        $application = new Application();
        $application->add($this->createCommand($crawler));

        $command       = $application->find('zenstruck:http-cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/Beginning http cache warmup./', $commandTester->getDisplay());
        $this->assertRegExp('/Summary:/', $commandTester->getDisplay());
        $this->assertRegExp('/200\s+\|\s+OK\s+\|\s+2/', $commandTester->getDisplay());
        $this->assertRegExp('/404\s+\|\s+Not Found\s+\|\s+1/', $commandTester->getDisplay());
        $this->assertRegExp('/Total\s+\|\s+3/', $commandTester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No URL providers registered.
     */
    public function testExecuteNoUrlProviders()
    {
        $crawler = $this->getMock('Zenstruck\CacheBundle\Url\Crawler', array(), array(), '', false);
        $crawler
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $application = new Application();
        $application->add($this->createCommand($crawler));

        $command       = $application->find('zenstruck:http-cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
    }

    private function createCommand($crawler)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with('zenstruck_cache.crawler')
            ->willReturn($crawler);

        $command = new HttpCacheWarmupCommand();
        $command->setContainer($container);

        return $command;
    }
}
