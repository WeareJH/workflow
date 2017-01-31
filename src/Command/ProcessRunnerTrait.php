<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait ProcessRunnerTrait
{
    /**
     * @var null|ProcessBuilder
     */
    private $processBuilder;

    private function checkProcess()
    {
        if (null === $this->processBuilder) {
            throw new \RuntimeException('Process builder isn\'t available');
        }
    }

    private function runProcessNoOutput(array $args): Process
    {
        $this->checkProcess();
        $this->processBuilder->setArguments($args);
        $process = $this->processBuilder->setTimeout(null)->getProcess();
        $process->run();

        return $process;
    }

    private function runProcessShowingOutput(OutputInterface $output, array $args)
    {
        $this->checkProcess();
        $this->processBuilder->setArguments($args);
        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
    }
}
