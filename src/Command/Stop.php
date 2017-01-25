<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $this
            ->setName('stop')
            ->setDescription('Stops the containers running')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Use when started with --prod / -p');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('prod')) {
            system('docker-compose -f docker-compose.yml -f docker-compose.prod.yml down');
            return;
        }

        system('docker-compose -f docker-compose.yml -f docker-compose.dev.yml down');

        $output->writeln('Containers stopped');
    }
}
