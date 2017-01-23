<?php

namespace Jh\Workflow;

use Composer\Script\Event;
use Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class CommandRouter
{
    public static $routes = [
        'watch'             => Commands\Watch::class,
        'sync'              => Commands\Sync::class,
        'start'             => Commands\Start::class,
        'stop'              => Commands\Stop::class,
        'build'             => Commands\Build::class,
        'up'                => Commands\Up::class,
        'push'              => Commands\Push::class,
        'pull'              => Commands\Pull::class,
        'composer-update'   => Commands\ComposerUpdate::class,
        'cu'                => Commands\ComposerUpdate::class,
        'magento'           => Commands\Magento::class,
        'mi'                => Commands\MagentoFullInstall::class,
        'magento-install'   => Commands\MagentoInstall::class,
        'magento-configure' => Commands\MagentoConfigure::class,
        'nginx-reload'      => Commands\NginxReload::class,
        'xdebug-loopback'   => Commands\XdebugLoopback::class,
        'test'              => Commands\Test::class,
        'help'              => Commands\Help::class,
    ];

    public static function route(Event $event)
    {
        $arguments = $event->getArguments();

        if (!count($arguments)) {
            throw new \InvalidArgumentException('You must supply a sub command, try the help command.');
        }

        $command = strtolower(array_shift($arguments));

        if (!array_key_exists($command, self::$routes)) {
            throw new \InvalidArgumentException('Not a valid command, try the help command.');
        }

        echo "\n";
        (new self::$routes[$command])($arguments);
        echo "\n";
    }
}
