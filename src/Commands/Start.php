<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Start implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        (new Build)($arguments);
        (new Up)($arguments);
        (new Watch)($arguments);
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
