<?php

namespace Jh\Workflow;

use React\EventLoop\LoopInterface;
use Rx\React\ProcessSubject;
use Rx\Subject\Subject;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ProcessFactory
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function create(string $command, int $timeout = null) : Process
    {
        return (new Process($command))->setTimeout($timeout);
    }

    public function createAsynchronous(
        string $command,
        string $workingDirectory,
        callable $onNext,
        callable $onComplete = null,
        callable $errors = null
    ) : ProcessSubject {
        $errorSubject = new Subject;
        $errorSubject->subscribe($errors);
        $process = new ProcessSubject($command, $errorSubject, $workingDirectory, [], [], $this->loop);
        $process->subscribe($onNext, null, $onComplete);

        return $process;
    }
}
