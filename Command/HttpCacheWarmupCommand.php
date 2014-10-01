<?php

namespace Zenstruck\CacheBundle\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zenstruck\CacheBundle\Crawler;
use Zenstruck\CacheBundle\Http\Response;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommand extends Command
{
    private $crawler;
    private $logger;

    public function __construct(Crawler $crawler, LoggerInterface $logger = null)
    {
        $this->crawler = $crawler;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zenstruck:http-cache:warmup')
            ->setDefinition(
                array(
                    new InputOption('parallel-requests', 'p', InputOption::VALUE_REQUIRED | InputOption::VALUE_REQUIRED, 'The number of requests to send in parallel', '10'),
                    new InputOption('timeout', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_REQUIRED, 'The timeout in seconds', '10'),
                    new InputOption('follow-redirects', 'r', InputOption::VALUE_NONE, 'Follow redirects?')
                )
            )
            ->setDescription('Warms up an http cache')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command warms up the http cache.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parallelRequests = (int) $input->getOption('parallel-requests');
        $timeout = (int) $input->getOption('timeout');
        $followRedirects = $input->getOption('follow-redirects');
        $summary = array();
        $total = $this->crawler->count();
        $progress = new ProgressBar($output, $total);
        $logger = $this->logger;

        if (0 === $total) {
            throw new \RuntimeException('No URL providers registered.');
        }

        $output->writeln("\n<comment>Beginning http cache warmup.</comment>");
        $progress->start();

        $callback = function (Response $response) use (&$summary, $progress, $logger) {
            $status = $response->getStatusCode();

            if (!array_key_exists($status, $summary)) {
                $summary[$status] = 1;
            } else {
                $summary[$status]++;
            }

            if ($logger) {
                $logger->log($status == 200 ? LogLevel::DEBUG : LogLevel::NOTICE, sprintf('[%s] %s', $status, $response->getUrl()));
            }

            $progress->advance();
        };

        $this->crawler->crawl($parallelRequests, $followRedirects, $timeout, $callback);

        $progress->finish();
        $output->writeln("\n");

        ksort($summary);

        $output->writeln("<comment>Summary:</comment>");

        $table = new Table($output);

        $table->setHeaders(array('Code', 'Reason', 'Count'));

        foreach ($summary as $code => $count) {
            $table->addRow(array($code, Response::getStatusText($code) ,$count));
        }

        $table->addRow(new TableSeparator());
        $table->addRow(array('', 'Total', $total));

        $table->render();
        $output->writeln('');
    }
}
