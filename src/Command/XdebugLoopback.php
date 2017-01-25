<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class XdebugLoopback extends Command implements CommandInterface
{
    public function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Starts the network loopback to allow Xdebug from Docker');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        `sudo ifconfig lo0 alias 10.254.254.254`;
    }
}
