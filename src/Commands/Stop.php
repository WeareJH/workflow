<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Stop extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        if (count($arguments) > 0 && '-p' === $arguments[0]) {
            `docker-compose -f docker-compose.yml -f docker-compose.prod.yml down -d`;
            return;
        }

        `docker-compose -f docker-compose.yml -f docker-compose.dev.yml down -d`;
    }

    public function getHelpText(): string
    {
        return <<<HELP
Stops the containers running for development

Use argument -p to stop in production mode
HELP;
    }
}
