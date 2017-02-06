<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\ProcessFactory;
use Jh\Workflow\ProcessFailedException;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ProcessRunnerTraitTest extends AbstractTestCommand
{
    private $badImplementation;

    public function setUp()
    {
        parent::setUp();

        $this->badImplementation = new class($this->output->reveal()) {
            use ProcessRunnerTrait;

            private $output;

            public function __construct(OutputInterface $output)
            {
                $this->output = $output;
            }

            public function runProcessNoOutputTest()
            {
                $this->runProcessNoOutput('');
            }

            public function runProcessShowingOutputTest()
            {
                $this->runProcessShowingOutput($this->output, '');
            }

            public function runProcessShowingErrorsTest()
            {
                $this->runProcessShowingOutput($this->output, '');
            }
        };
    }

    public function testRunProccessNoOutputThrowsExceptionWithNoBuilder()
    {
        $this->expectException(\RuntimeException::class);
        $this->badImplementation->runProcessNoOutputTest();
    }

    public function testRunProcessShowingOutputThrowsExceptionWithNoBuilder()
    {
        $this->expectException(\RuntimeException::class);
        $this->badImplementation->runProcessShowingOutputTest();
    }

    public function testRunProcessShowingErrorsTestThrowsExceptionWithNoBuilder()
    {
        $this->expectException(\RuntimeException::class);
        $this->badImplementation->runProcessShowingErrorsTest();
    }

    public function testExceptionIsThrownIfCommandShowingOutputFailsToFinishCorrectly()
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->prophesize(Process::class);
        $process->run(Argument::type('callable'))->willReturn(1);
        $processFactory = $this->prophesize(ProcessFactory::class);
        $processFactory->create('i-am-dead')->willReturn($process);


        $implementation = new class ($processFactory->reveal(), $this->output->reveal()) {
            use ProcessRunnerTrait;

            private $output;

            public function __construct(ProcessFactory $processFactory, OutputInterface $output)
            {
                $this->processFactory = $processFactory;
                $this->output = $output;
            }

            public function runCommand($cmd)
            {
                $this->runProcessShowingOutput($this->output, $cmd);
            }
        };

        $implementation->runCommand('i-am-dead');
    }

    public function testExceptionIsThrownIfCommandFailsToFinishCorrectly()
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->prophesize(Process::class);
        $process->run()->willReturn(1);
        $processFactory = $this->prophesize(ProcessFactory::class);
        $processFactory->create('i-am-dead')->willReturn($process);


        $implementation = new class ($processFactory->reveal()) {
            use ProcessRunnerTrait;

            public function __construct(ProcessFactory $processFactory)
            {
                $this->processFactory = $processFactory;
            }

            public function runCommand($cmd)
            {
                $this->runProcessNoOutput($cmd);
            }
        };

        $implementation->runCommand('i-am-dead');
    }
}
