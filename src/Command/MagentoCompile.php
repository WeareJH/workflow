<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class MagentoCompile extends Command implements CommandInterface
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
            ->setName('magento-compile')
            ->setDescription('Runs the magento DI compile command and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $this->commandLine->run(sprintf('docker exec -u www-data %s bin/magento setup:di:compile --ansi', $container));

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['var/di', 'var/generation']]);

        $pullCommand->run($pullArguments, $output);
    }
}
