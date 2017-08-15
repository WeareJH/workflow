<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class NginxReload extends Command implements CommandInterface
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
            ->setName('nginx-reload')
            ->setAliases(['nginx'])
            ->setDescription('Sends reload signal to NGINX in the container');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandLine->run(sprintf('docker exec %s nginx -s "reload"', $this->getContainerName('nginx')));

        $output->writeln('Reload signal sent');
    }
}
