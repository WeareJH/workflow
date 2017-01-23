<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        $watches  = ['./app', './pub', './composer.json'];
        $excludes = ['.docker', '.*__jp*', '.swp', '.swpx'];

        system(sprintf(
            'fswatch -r %s -e \'%s\' | xargs -n1 -I{} composer run sync {}',
            implode(' ', $watches),
            implode('|', $excludes)
        ));

        echo 'Watching for file changes...';
    }

    public function getHelpText(): string
    {
        return 'Keeps track of filesystem changes, piping the changes to the Sync command';
    }
}
