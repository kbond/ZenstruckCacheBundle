<?php

namespace Zenstruck\CacheBundle\Command;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\CacheBundle\Url\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zenstruck:http-cache:warmup')
            ->setDescription('Warms up an http cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Crawler $crawler */
        $crawler  = $this->getContainer()->get('zenstruck_cache.crawler');
        $summary  = array();
        $total    = count($crawler);
        $progress = new ProgressBar($output, $total);

        if (0 === $total) {
            throw new \RuntimeException('No URL providers registered.');
        }

        $output->writeln("\n<comment>Beginning http cache warmup.</comment>");
        $progress->start();

        $callback = function (ResponseInterface $response) use (&$summary, $progress) {
            $status = $response->getStatusCode();

            $progress->advance();

            if (!array_key_exists($status, $summary)) {
                $summary[$status] = 1;

                return;
            }

            ++$summary[$status];
        };

        $crawler->crawl($callback);

        $progress->finish();
        $output->writeln("\n");

        ksort($summary);

        $output->writeln('<comment>Summary:</comment>');

        $table = new Table($output);

        $table->setHeaders(array('Code', 'Reason', 'Count'));

        foreach ($summary as $code => $count) {
            $table->addRow(array($code, Response::$statusTexts[$code], $count));
        }

        $table->addRow(new TableSeparator());
        $table->addRow(array('', 'Total', $total));

        $table->render();
        $output->writeln('');
    }
}
