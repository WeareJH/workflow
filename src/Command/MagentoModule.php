<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoModule extends Command implements CommandInterface
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
            ->setName('module')
            ->setDescription('Manage Magento modules and updates the config.php file')
            ->addArgument('module', InputArgument::REQUIRED, 'Module to manage')
            ->addOption('enable', 'e', InputOption::VALUE_NONE, 'Enable the module')
            ->addOption('disable', 'd', InputOption::VALUE_NONE, 'Disable the module');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $module    = $input->getArgument('module');
        $action    = 'status';

        if ($input->getOption('enable')) {
            $action = 'enable';
        } elseif ($input->getOption('disable')) {
            $action = 'disable';
        }

        $command = sprintf('docker exec -u www-data %s bin/magento module:%s %s --ansi', $container, $action, $module);
        $this->runProcessShowingOutput($output, $command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/config.php']]);

        $pullCommand->run($pullArguments, $output);
    }
}
