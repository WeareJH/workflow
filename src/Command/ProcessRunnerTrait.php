<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\ProcessFactory;
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

    private function runProcessNoOutput(string $command): Process
    {
        $this->checkProcess();
        $process = $this->processFactory->create($command);
        $process->run();

        return $process;
    }

    private function runProcessShowingOutput(OutputInterface $output, string $command)
    {
        $this->checkProcess();

        $this->processFactory->create($command)->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
    }
}
