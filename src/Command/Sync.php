<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sync extends Command implements CommandInterface
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

        $command = sprintf('docker exec %s rm -rf /var/www/%s', $container, $containerPath);
        $this->runProcessShowingErrors($output, explode(' ', $command));

        $output->writeln("<fg=red> x $containerPath > $container </fg=red>");
    }
}
