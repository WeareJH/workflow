<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class VarnishEnable extends Command implements CommandInterface
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
            ->setName('varnish-enable')
            ->setAliases(['ve'])
            ->setDescription('Switches the VCL to use caching');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandLine->run('docker-compose exec varnish varnishadm vcl.use boot0');

        $output->writeln('Varnish caching enabled');
    }
}
