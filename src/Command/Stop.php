<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessBuilder $processBuilder)
    {
        parent::__construct();
        $this->processBuilder = $processBuilder;
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
        $envDockerFile = $input->hasOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $this->runProcessShowingOutput($output, [
            'docker-compose',
            '-f docker-compose.yml',
            '-f ' . $envDockerFile,
            'down'
        ]);

        $output->writeln('<info>Containers stopped</info>');
    }
}
