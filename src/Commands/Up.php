<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        if (count($arguments) > 0 && 'prod' === $arguments[0]) {
            system('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d');
            return;
        }

        system('docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d');
    }

    public function getHelpText(): string
    {
        return <<<HELP
Uses docker-compose to start the containers for development

Use argument prod to start in production mode 

Usage: composer x up [prod]
HELP;
    }
}
