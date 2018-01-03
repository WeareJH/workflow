<?php

namespace Jh\Workflow;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    public function logCommand(string $command, string $type);
}
