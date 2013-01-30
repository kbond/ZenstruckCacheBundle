<?php

namespace Zenstruck\Bundle\CacheBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpCacheWarmupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zenstruck:http-cache:warmup')
            ->setDefinition(array(
                new InputArgument('host', InputArgument::OPTIONAL, 'The full host - ie http://www.google.com')
            ))
            ->setDescription('Warms up an http cache')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command warms up the http cache.
EOF
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $registry \Zenstruck\Bundle\CacheBundle\HttpCache\WarmupRegistry */
        $registry = $this->getContainer()->get('zenstruck_cache.warmup_registry');

        if (!count($registry->getProviders())) {
            $output->writeln('No providers registered.');
            return;
        }

        $urls = $registry->getUrls($input->getArgument('host'));

        if (!count($urls)) {
            $output->writeln('No urls provided.');
            return;
        }

        // use the new ProgressHelper in Symfony 2.2
        $progress = null;
        if ($this->getHelperSet()->has('progress')) {
            $progress = $this->getHelper('progress');

            $progress->start($output, count($urls));
        }

        foreach ($urls as $url) {
            $http = curl_init($url);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($http);

            if ($input->getOption('verbose')) {
                $output->writeln($url);
            } else {
                if ($progress) {
                    $progress->advance();
                } else {
                    $output->write('.');
                }
            }
        }

        if ($progress) {
            $progress->finish();
        }
    }
}
