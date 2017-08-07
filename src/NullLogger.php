<?php

namespace Jh\Workflow;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class NullLogger extends \Psr\Log\NullLogger implements LoggerInterface
{

    public function logCommand(string $command, string $type)
    {
        //noop
    }
}