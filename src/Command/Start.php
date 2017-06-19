<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Start extends Command implements CommandInterface
{
    use DockerAwareTrait;

    public function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Runs build, up and watch comands')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $buildCommand = $this->getApplication()->find('build');
        $upCommand    = $this->getApplication()->find('up');
        $pullCommand  = $this->getApplication()->find('pull');
        $watchCommand = $this->getApplication()->find('watch');

        $buildCommand->run($input, $output);
        $upCommand->run($input, $output);
        $pullCommand->run(new ArrayInput(['files' => ['.docker/composer-cache']]), $output);
        $watchCommand->run($input, $output);
    }
}
