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
            ->setDescription('Runs build, up and watch commands')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Omits development configurations')
            ->addOption('mount', 'm', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Directories to mount at run time');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $buildCommand = $this->getApplication()->find('build');
        $upCommand    = $this->getApplication()->find('up');
        $pullCommand  = $this->getApplication()->find('pull');
        $watchCommand = $this->getApplication()->find('watch');

        $buildCommand->run(new ArrayInput([
            new InputOption('prod', $input->getOption('prod'))
        ]), $output);

        $upCommand->run($input, $output);
        $pullCommand->run(new ArrayInput(['files' => ['.docker/composer-cache']]), $output);

        if (count($input->getOption('mount')) === 0) {
            $watchCommand->run($input, $output);
        }
    }
}
