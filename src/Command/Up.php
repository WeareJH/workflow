<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up extends Command implements CommandInterface
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
            ->setName('up')
            ->setAliases(['start'])
            ->setDescription('Uses docker-compose to start the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Omits development configurations')
            ->addOption('no-build', null, InputOption::VALUE_NONE, 'Prevents running a full build');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $buildArg = $input->getOption('no-build') ? '' : '--build';

        $composeFiles = $this->getComposeFileFlags($input->getOption('prod') ? true : false);

        $this->commandLine->run(rtrim(sprintf('docker-compose %s up -d %s', $composeFiles, $buildArg)));

        // Pull composer cache for future builds
        $pullCommand  = $this->getApplication()->find('pull');
        $pullCommand->run(new ArrayInput(['files' => ['.docker/composer-cache']]), $output);

        $output->writeln('<info>Containers started</info>');
    }
}
