<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class NginxReload extends Command implements CommandInterface
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
            ->setName('nginx-reload')
            ->setAliases(['nginx'])
            ->setDescription('Sends reload signal to NGINX in the container');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('nginx');

        $command = sprintf('docker exec %s nginx -s "reload"', $container);
        $this->runProcessShowingOutput($output, $command);

        $output->writeln('Reload signal sent');
    }
}
