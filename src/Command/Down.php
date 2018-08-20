<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Max Bucknell <max@wearejh.com>
 */
class Down extends Command implements CommandInterface
{
    use DockerAwareTrait;

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
            ->setName('down')
            ->setDescription('Stop and remove containers, networks, images, and volumes')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Use when started with --prod / -p');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $composeFiles = $this->getComposeFileFlags($input->getOption('prod') ? true : false);

        $this->commandLine->run(sprintf('docker-compose %s down', $composeFiles));

        $output->writeln('<info>Containers stopped</info>');
    }
}
