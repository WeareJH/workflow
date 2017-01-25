<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop extends Command implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        if (count($arguments) > 0 && 'prod' === $arguments[0]) {
            system('docker-compose -f docker-compose.yml -f docker-compose.prod.yml down');
            return;
        }

        system('docker-compose -f docker-compose.yml -f docker-compose.dev.yml down');
    }

    public function getHelpText(): string
    {
        return <<<HELP
Stops the containers running for development

Use argument prod to stop in production mode

Usage: composer run stop [prod]
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
