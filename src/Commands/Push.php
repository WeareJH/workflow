<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        // TODO: Implement __invoke() method.
    }

    public function getHelpText(): string
    {
        return <<<HELP
Push files from host to the relevant docker containers. Useful for when the watch isn't running or you watch to push loads of files in quickly

Usage: composer x push source_file\033[2m
Where x is the composer script used in your project and source_file is relative to the app path \033[22m
HELP;
    }
}
