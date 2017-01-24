<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop implements CommandInterface
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
}
