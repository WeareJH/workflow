<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up extends Command implements CommandInterface
{
    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        parent::__construct();
        $this->commandLine = $commandLine;
    }

    public function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Uses docker-compose to start the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Omits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $envDockerFile = $input->getOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $this->commandLine->run(sprintf('docker-compose -f docker-compose.yml -f %s up -d', $envDockerFile));

        $output->writeln('<info>Containers started</info>');
    }
}
