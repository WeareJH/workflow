<?php

namespace Jh\Workflow;

use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ProcessFactory
{
    public function create(string $command, int $timeout = null) : Process
    {
        return (new Process($command))->setTimeout($timeout);
    }
}
