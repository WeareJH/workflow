<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoModuleEnable extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    public function configure()
    {
        $this
            ->setName('module:enable')
            ->setDescription('Enable Magento module and updates the config.php file')
            ->addArgument('module', InputArgument::REQUIRED, 'Module to enable');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $module    = $input->getArgument('module');

        $command = sprintf('docker exec -u www-data %s bin/magento module:enable %s --ansi', $container, $module);
        $this->runProcessShowingOutput($output, $command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/config.php']]);

        $pullCommand->run($pullArguments, $output);
    }
}
