<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Start extends Command implements CommandInterface
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

Use argument prod to start in production mode 
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
