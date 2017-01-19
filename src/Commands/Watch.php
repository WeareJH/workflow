<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch implements \CommandInterface
{
    public function invoke(array $containers, array $arguments)
    {
        exec('fswatch -r ./app ./pub ./composer.json -e \'.docker|.*__jp*\' | xargs -n1 -I{} composer run sync {}');
    }
}
