<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class VarnishDisable extends Command implements CommandInterface
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
            ->setName('varnish-disable')
            ->setAliases(['vd'])
            ->setDescription('Switches the VCL to be a proxy');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('varnish');

        $this->commandLine->run(sprintf(
            'docker exec -t %s varnishadm vcl.use boot',
            $container
        ));

        $output->writeln('<info>Varnish caching disabled</info>');
    }
}
