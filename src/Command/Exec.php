<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Exec extends Command implements CommandInterface
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
            ->setName('exec')
            ->setDescription('Run an arbitrary command on the app container')
            ->addArgument('command-line', InputArgument::REQUIRED, 'Command to execute')
            ->addOption(
                'root',
                'r',
                InputOption::VALUE_NONE,
                'Exec as root user (must be passed before command e.g workflow exec -r ls -la)'
            )
            ->ignoreValidationErrors();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = $this->phpContainerName();
        $slicePoint = 1 + (int) array_search($this->getName(), $_SERVER['argv'], true);

        $root = false;
        if ($_SERVER['argv'][$slicePoint] === '-r' || $_SERVER['argv'][$slicePoint] === '--root') {
            $root = true;
            $slicePoint++;
        }

        $args       = array_slice($_SERVER['argv'], $slicePoint);
        $user       = $root ? 'root' : 'www-data';
        $command    = sprintf('docker exec -it -u %s %s %s', $user, $container, implode(' ', $args));

        $this->commandLine->runInteractively($command);
    }
}
