<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class XdebugLoopback extends Command implements CommandInterface
{
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
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
        $this->runProcessShowingOutput($output, 'sudo ifconfig lo0 alias 10.254.254.254');
    }
}
