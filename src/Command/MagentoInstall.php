<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstall extends Command implements CommandInterface
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
            ->setName('magento-install')
            ->setAliases(['mi'])
            ->setDescription('Runs the magento install script');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandLine->run(sprintf('docker exec -u www-data %s magento-install', $this->phpContainerName()));

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc']]);

        $pullCommand->run($pullArguments, $output);

        $output->writeln('Install complete!');
    }
}
