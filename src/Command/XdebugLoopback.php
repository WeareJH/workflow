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

    public function __invoke(array $arguments)
    {
        `sudo ifconfig lo0 alias 10.254.254.254`;
    }

    public function getHelpText(): string
    {
        return <<<HELP
Starts the network loopback to allow Xdebug from Docker'

Runs as sudo, requires password input
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
