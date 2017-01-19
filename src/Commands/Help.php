<?php

namespace Jh\Workflow\Commands;
use Jh\Workflow\CommandRouter;

/**
 * Class Help
 */
class Help implements \CommandInterface
{
    public function invoke(array $containers, array $arguments)
    {
        echo <<<HELP
  
  JH Workflow Commands
  --------------------

  Watch
    Keeps track of filesystem changes, piping the changes to the Sync command.

  Sync
    Pushes changes from the filesystem to the relevant docker containers. 
    
    - Nginx will take changes from the pub directory
    - PHP will take changes from all directories except .docker.

  Start
    Runs 3 commands

    - Build
    - Up
    - Watch
HELP;
    }
}
