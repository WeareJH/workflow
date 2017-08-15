<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Restart extends Command implements CommandInterface
{
    public function configure()
    {
        $this
            ->setName('restart')
            ->setDescription('Restarts the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Use when started with --prod / -p');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stopCommand = $this->getApplication()->find('stop');
        $upCommand   = $this->getApplication()->find('up');

        $stopCommand->run($input, $output);
        $upCommand->run($input, $output);
    }
}
