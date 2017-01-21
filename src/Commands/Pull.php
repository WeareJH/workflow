<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Pull extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        // TODO: Implement __invoke() method.
    }

    public function getHelpText(): string
    {
        return <<<HELP
Pull files from the docker environment to the host, Useful for pulling vendor, composer_cache etc

If the watch is running and you pull a file that is being watched it will automatically be pushed back into the container.
If this is not what you want (large dirs can cause issues here) stop the watch, pull then start the watch again afterwards.

Usage: composer x pull source_file \033[2m
Where x is the composer script used in your project and source_file is relative to the app path \033[22m
HELP;
    }
}
