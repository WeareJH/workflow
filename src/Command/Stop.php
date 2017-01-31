<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    public function configure()
    {
        $this
            ->setName('stop')
            ->setDescription('Stops the containers running')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Use when started with --prod / -p');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $envDockerFile = $input->getOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $command = sprintf('docker-compose -f docker-compose.yml -f %s down', $envDockerFile);
        $this->runProcessShowingOutput($output, $command);

        $output->writeln('<info>Containers stopped</info>');
    }
}
