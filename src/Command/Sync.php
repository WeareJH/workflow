<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sync extends Command implements CommandInterface
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
            ->setName('sync')
            ->setDescription('Syncs changes from the host filesystem to the relevant docker containers')
            ->addArgument('file', InputArgument::REQUIRED, 'The changed file path');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path          = $input->getArgument('file');
        $containerPath = ltrim(str_replace(getcwd(), '', $path), '/');
        $container     = $this->phpContainerName();

        if (file_exists($path)) {
            $pushCommand   = $this->getApplication()->find('push');
            $pushArguments = new ArrayInput(['files' => [$path]]);

            $pushCommand->run($pushArguments, $output);
            return;
        }

        $this->commandLine->run(sprintf('docker exec %s rm -rf /var/www/%s', $container, $containerPath));

        $output->writeln("<fg=red> x $containerPath > $container </fg=red>");
    }
}
