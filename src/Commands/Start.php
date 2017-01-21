<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Start extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        // TODO: Implement __invoke() method.
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs 3 commands

- build
- up
- watch

Use argument -p to start in production mode 
HELP;
    }
}
