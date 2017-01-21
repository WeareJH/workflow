<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        `fswatch -r ./app ./pub ./composer.json -e \'.docker|.*__jp*\' | xargs -n1 -I{} composer run sync {}`;
    }

    public function getHelpText(): string
    {
        return 'Keeps track of filesystem changes, piping the changes to the Sync command';
    }
}
