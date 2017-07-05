<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\ProcessFactory;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use Rx\React\ProcessSubject;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends TestCase
{
    public function testSynchronousProcess()
    {
        $loop = $this->prophesize(LoopInterface::class);

        $process = (new ProcessFactory($loop->reveal()))
            ->create('ls');

        self::assertInstanceOf(Process::class, $process);
    }

    public function testAsynchronousProcess()
    {
        $loop = $this->prophesize(LoopInterface::class);

        $process = (new ProcessFactory($loop->reveal()))
            ->createAsynchronous('ls', __DIR__, function () {

            });

        self::assertInstanceOf(ProcessSubject::class, $process);
    }
}
