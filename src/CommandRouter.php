<?php

namespace Jh\Workflow;

use Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class CommandRouter
{
    public static $routes = [
        'help'  => Commands\Help::class,
        'watch' => Commands\Watch::class,
        'sync'  => Commands\Sync::class
    ];

    public static function route()
    {
        $arguments = array_slice($argv, 2);

        if (!count($arguments)) {
            throw new \InvalidArgumentException('You must supply a sub command, try the help command.');
        }

        $command = strtolower(array_shift($arguments));

        if (!array_key_exists($command, self::$routes)) {
            throw new \InvalidArgumentException('Not a valid command, try the help command.');
        }

        (new $command)($arguments);
    }
}
