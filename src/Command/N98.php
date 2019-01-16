<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Jh\Workflow\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class N98 extends Command implements CommandInterface
{
    use DockerAwareTrait;

    /**
     * @var CommandLine
     */
    private $commandLine;

    /**
     * @var Files
     */
    private $files;

    public function __construct(CommandLine $commandLine, Files $files)
    {
        parent::__construct();
        $this->commandLine = $commandLine;
        $this->files = $files;
    }

    protected function configure()
    {
        $this
            ->setName('n98')
            ->setDescription('Run N98 commands in the PHP container - downloads N98 if not present')
            ->addArgument('command-line', InputArgument::REQUIRED, 'N98 Command to execute')
            ->ignoreValidationErrors();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = $this->phpContainerName();

        if (!$this->files->existsInContainer($container, './n98-magerun2.phar')) {
            $command = sprintf('docker exec -it -u www-data %s curl -O https://files.magerun.net/n98-magerun2.phar', $container);
            $this->commandLine->runInteractively($command);
            $command = sprintf('docker exec -it -u www-data %s chmod +x ./n98-magerun2.phar', $container);
            $this->commandLine->runInteractively($command);
        }

        $command = sprintf(
            'docker exec -it -u www-data %s ./n98-magerun2.phar %s',
            $container,
            $input->getArgument('command-line')
        );

        $this->commandLine->runInteractively($command);
    }
}
