<?php

namespace Jh\Workflow;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $logFile;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->logFile = getenv('HOME') . '/workflow.log';
        $this->init();
        $this->output = $output;
    }

    public function log($level, $message, array $context = [])
    {
        $dateTime = (new \DateTime)->format('d-m-y H:i:s');
        $line = sprintf("%s (%s) %s\n", $dateTime, strtoupper($level), $message);
        file_put_contents(
            $this->logFile,
            $line,
            FILE_APPEND | LOCK_EX
        );
    }

    public function logCommand(string $command, string $type)
    {
        $this->debug(sprintf('Executing command [%s]: "%s"', $type, $command));

        $this->output->writeln(
            sprintf(
                'Executing [<comment>%s</comment>] <question>%s</question>',
                $type,
                $command
            )
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
