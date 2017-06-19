<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\ProcessFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class AbstractTestCommand extends TestCase
{
    /**
     * @var Prophet
     */
    protected $prophet;

    /**
     * @var ObjectProphecy|InputInterface
     */
    protected $input;

    /**
     * @var ObjectProphecy|OutputInterface
     */
    protected $output;

    /**
     * @var ObjectProphecy|Process
     */
    protected $process;

    /**
     * @var ObjectProphecy|ProcessFactory
     */
    protected $processFactory;

    public function setUp()
    {
        $this->prophet = new Prophet();

        $this->input  = $this->prophesize(ArgvInput::class);
        $this->output = $this->prophesize(Output::class);

        $this->process        = $this->prophesize(Process::class);
        $this->processFactory = $this->prophesize(ProcessFactory::class);
    }

    protected function processTest(string $expected)
    {
        $this->processFactory->create($expected)->willReturn($this->process->reveal())->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();
    }

    protected function processTestNoOutput(string $expected)
    {
        $this->processFactory->create($expected)->willReturn($this->process->reveal())->shouldBeCalled();

        $this->process->run()->shouldBeCalled();
    }

    protected function useInvalidEnvironment()
    {
        chdir(__DIR__ . '/../fixtures/invalid-env');
    }

    protected function useValidEnvironment()
    {
        chdir(__DIR__ . '/../fixtures/valid-env');
    }

    protected function useBrokenEnvironemt()
    {
        chdir(__DIR__ . '/../fixtures/broken-env');
    }
}
