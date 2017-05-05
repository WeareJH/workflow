<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class MagentoCompile extends Command implements CommandInterface
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
            ->setName('magento-compile')
            ->setDescription('Runs the magento DI compile command and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $command = sprintf('docker exec -u www-data %s bin/magento setup:di:compile --ansi', $container);
        $this->runProcessShowingOutput($output, $command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['var/di', 'var/generation']]);

        $pullCommand->run($pullArguments, $output);
    }
}
