<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class XdebugLoopback implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        `sudo ifconfig lo0 alias 10.254.254.254`;
    }

    public function getHelpText(): string
    {
        return <<<HELP
Starts the network loopback to allow Xdebug from Docker';

Runs as sudo, requires password input
HELP;
    }
}
