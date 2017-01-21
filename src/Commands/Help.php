<?php

namespace Jh\Workflow\Commands;
use Jh\Workflow\CommandRouter;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Help implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        $commands = array_unique(CommandRouter::$routes);
        $aliases  = array_diff(CommandRouter::$routes, $commands);
        $width    = exec('tput cols') - 8;

        echo "\n\033[1m  JH Workflow Commands  \033[22m\n\n";

        foreach ($commands as $commandName => $command) {
            /** @var CommandInterface $command */
            $command  = new $command;
            $helpText = implode("\n    ", explode("\n", trim(wordwrap($command->getHelpText(), $width))));

            echo sprintf("\033[32m  %s \033[39m\n", $commandName);
            echo sprintf("    %s \n\n", $helpText);
        }
    }

    public function getHelpText() : string
    {
        return 'Display this help text';
    }
}
