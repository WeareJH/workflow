<?php

namespace Jh\Workflow;

use React\EventLoop\LoopInterface;
use Rx\React\ProcessSubject;
use Rx\Subject\Subject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class CommandLine
{
    /**
     * @var LoopInterface
     */
    private $eventLoop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(LoopInterface $eventLoop, LoggerInterface $logger, OutputInterface $output)
    {
        $this->eventLoop = $eventLoop;
        $this->logger = $logger;
        $this->output = $output;
    }

    public function run(string $command) : string
    {
        $this->logCommand($command, 'normal');

        return $this->runProcess($this->newProcess($command), $this->onOutput());
    }

    public function runQuietly(string $command) : string
    {
        $this->logCommand($command, 'quiet');

        return $this->runProcess($this->newProcess($command));
    }

    public function runInteractively(string $command) : string
    {
        $this->logCommand($command, 'interactive');

        $process = $this->newProcess($command);
        $process->setTty(true);

        return $this->runProcess($this->newProcess($command), $this->onOutput());
    }

    private function newProcess(string $command) : Process
    {
        $process = new Process($command);
        $process->setTimeout(null);
        return $process;
    }

    private function runProcess(Process $process, $onOutput = null) : string
    {
        $exitCode = $process->run($onOutput);

        if ($exitCode > 0) {
            throw new ProcessFailedException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    private function onOutput() : callable
    {
        return function ($type, $buffer) {
            $this->output->write($buffer);
        };
    }

    public function runAsync(string $command, callable $onComplete = null)
    {
        $this->logCommand($command, 'async');

        $errorSubject = new Subject;
        $errorSubject->subscribe(function (\Exception $e) {
            throw new ProcessFailedException($e->getMessage());
        });

        $process = new ProcessSubject($command, $errorSubject, null, [], [], $this->eventLoop);
        $process->subscribe(
            function ($buffer) {
                $this->output->write($buffer);
            },
            null,
            $onComplete
        );
    }

    private function logCommand(string $command, string $type)
    {
        $this->logger->logCommand($command, $type);
    }

    public function commandExists(string $executable) : bool
    {
        return (bool) (new ExecutableFinder)->find($executable, false);
    }
}