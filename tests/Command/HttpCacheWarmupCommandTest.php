<?php

namespace Zenstruck\CacheBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\CacheBundle\Command\HttpCacheWarmupCommand;
use Zenstruck\CacheBundle\Tests\TestCase;
use Zenstruck\CacheBundle\Url\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommandTest extends TestCase
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
            ->willReturn(['foo.com', 'bar.com', 'baz.com']);

        $request = $this->mockRequest();

        $messageFactory = $this->mockMessageFactory();
        $messageFactory
            ->expects($this->at(0))
            ->method('createRequest')
            ->with('GET', 'foo.com')
            ->willReturn($request);

        $messageFactory
            ->expects($this->at(1))
            ->method('createRequest')
            ->with('GET', 'bar.com')
            ->willReturn($request);

        $messageFactory
            ->expects($this->at(2))
            ->method('createRequest')
            ->with('GET', 'baz.com')
            ->willReturn($request);

        $httpClient = $this->mockHttpClient();
        $httpClient
            ->expects($this->at(0))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 200));

        $httpClient
            ->expects($this->at(1))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 200));

        $httpClient
            ->expects($this->at(2))
            ->method('sendRequest')
            ->with($request)
            ->willReturn($this->mockResponse('', 404));

        $crawler = new Crawler($httpClient, $messageFactory, null, [$provider]);

        $application = new Application();
        $application->add($this->createCommand($crawler));

        $command = $application->find('zenstruck:http-cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

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
        $crawler = $this->getMock('Zenstruck\CacheBundle\Url\Crawler', [], [], '', false);
        $crawler
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $application = new Application();
        $application->add($this->createCommand($crawler));

        $command = $application->find('zenstruck:http-cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
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
