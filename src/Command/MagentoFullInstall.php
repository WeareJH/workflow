<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoFullInstall extends Command implements CommandInterface
{
    use DockerAwareTrait;

    public function configure()
    {
        $this
            ->setName('magento-full-install')
            ->setAliases(['mfi'])
            ->setDescription('Runs magento-install and magento-configure commands')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $installCommand   = $this->getApplication()->find('magento-install');
        $configureCommand = $this->getApplication()->find('magento-configure');

        $installCommand->run($input, $output);
        $configureCommand->run($input, $output);
    }
}
