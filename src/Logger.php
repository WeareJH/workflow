<?php

namespace Jh\Workflow;

use Psr\Log\AbstractLogger;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Logger extends AbstractLogger
{
    /**
     * @var string
     */
    private $logFile;

    public function __construct()
    {
        $this->logFile = getenv('HOME') . '/workflow.log';
        $this->init();
    }

    public function log($level, $message, array $context = [])
    {
        file_put_contents(
            $this->logFile,
            sprintf("%s (%s) %s\n", (new \DateTime)->format('d-m-y H:i:s'), strtoupper($level), $message),
            FILE_APPEND | LOCK_EX
        );
    }

    private function init()
    {
        @mkdir(dirname($this->logFile), 0777, true);
    }

    /**
     * For testing
     */
    public function setLogFile(string $logFile)
    {
        $this->logFile = $logFile;
        $this->init();
    }
}