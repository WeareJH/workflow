<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class XdebugLoopback extends Command implements CommandInterface
{
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
            ->setName('xdebug-loopback')
            ->setAliases(['xdebug'])
            ->setDescription('Starts the network loopback to allow Xdebug from Docker');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandLine->run('sudo ifconfig lo0 alias 10.254.254.254');
    }
}
