<?php

namespace Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\CacheBundle\Command\HttpCacheWarmupCommand;
use Zenstruck\CacheBundle\Crawler;
use Zenstruck\CacheBundle\Http\Response;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $provider = $this->getMock('Zenstruck\CacheBundle\UrlProvider\UrlProvider');
        $provider
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(3));
        $provider
            ->expects($this->once())
            ->method('getUrls')
            ->willReturn(array('http://foo.com', 'http://bar.com', 'http://baz.com'));

        $client = $this->getMock('Zenstruck\CacheBundle\Http\Client');
        $client
            ->expects($this->once())
            ->method('fetchMulti')
            ->with(array('http://foo.com', 'http://bar.com', 'http://baz.com'))
            ->will(
                $this->returnValue(
                    array(
                        new Response('http://foo.com', '', 200),
                        new Response('http://bar.com', '', 404),
                        new Response('http://baz.com', '', 200),
                    )
                )
            );

        $crawler = new Crawler($client, array($provider));

        $application = new Application();
        $application->add(new HttpCacheWarmupCommand($crawler));

        $command = $application->find('zenstruck:http-cache:warmup');
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
        $crawler = $this->getMock('Zenstruck\CacheBundle\Crawler', array(), array(), '', false);
        $crawler
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $application = new Application();
        $application->add(new HttpCacheWarmupCommand($crawler));

        $command = $application->find('zenstruck:http-cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));
    }
}
