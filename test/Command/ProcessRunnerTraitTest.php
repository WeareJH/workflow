<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Symfony\Component\Console\Output\OutputInterface;

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
}
