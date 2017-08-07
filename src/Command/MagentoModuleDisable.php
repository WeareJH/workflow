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
class MagentoModuleDisable extends Command implements CommandInterface
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
            ->setName('module:disable')
            ->setDescription('Disable Magento module and updates the config.php file')
            ->addArgument('module', InputArgument::REQUIRED, 'Module to disable');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $module    = $input->getArgument('module');

        $this->commandLine->run(
            sprintf('docker exec -u www-data %s bin/magento module:disable %s --ansi', $container, $module)
        );

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/config.php']]);

        $pullCommand->run($pullArguments, $output);
    }
}
