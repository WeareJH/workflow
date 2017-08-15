<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Php extends Command implements CommandInterface
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

    protected function configure()
    {
        $this
            ->setName('php')
            ->setDescription('Run a php script on the app container')
            ->addArgument('php-file', InputOption::VALUE_REQUIRED, 'Path to PHP file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandLine->runInteractively(
            sprintf(
                'docker exec -it -u www-data %s php %s',
                $this->phpContainerName(),
                $input->getArgument('php-file')
            )
        );
    }
}
