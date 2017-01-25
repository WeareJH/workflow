<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up extends Command implements CommandInterface
{
    public function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Uses docker-compose to start the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('prod')) {
            system('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d');
            return;
        }

        system('docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d');
    }
}
