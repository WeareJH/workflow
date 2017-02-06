<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\ProcessFactory;
use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait ProcessRunnerTrait
{
    /**
     * @var null|ProcessFactory
     */
    private $processFactory;

    private function checkProcess()
    {
        if (null === $this->processFactory) {
            throw new \RuntimeException('Process factory isn\'t available');
        }
    }

    /**
     * @param string $command
     * @return Process
     * @throws ProcessFailedException
     */
    private function runProcessNoOutput(string $command): Process
    {
        $this->checkProcess();
        $process = $this->processFactory->create($command);
        $exitCode = $process->run();

        if ($exitCode > 0) {
            throw new ProcessFailedException;
        }

        return $process;
    }

    /**
     * @param OutputInterface $output
     * @param string $command
     * @throws ProcessFailedException
     */
    private function runProcessShowingOutput(OutputInterface $output, string $command)
    {
        $this->checkProcess();

        $exitCode = $this->processFactory->create($command)->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if ($exitCode > 0) {
            throw new ProcessFailedException;
        }
    }
}
