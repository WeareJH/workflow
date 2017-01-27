<?php

namespace Jh\WorkflowTest\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

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
     * @var ObjectProphecy|ProcessBuilder
     */
    protected $processBuilder;

    public function setUp()
    {
        $this->prophet = new Prophet();

        $this->input  = $this->prophesize(ArgvInput::class);
        $this->output = $this->prophesize(Output::class);

        $this->process        = $this->prophesize(Process::class);
        $this->processBuilder = $this->prophesize(ProcessBuilder::class);

        $this->processBuilder->getProcess()->willReturn($this->process->reveal());
    }

    protected function processTest(array $expectedArgs, int $timeout = null)
    {
        $this->processBuilder->setArguments($expectedArgs)->willReturn($this->processBuilder);
        $this->processBuilder->setTimeout($timeout)->willReturn($this->processBuilder);

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->writeln('ERR > bad output')->shouldBeCalled();
        $this->output->writeln('good output')->shouldBeCalled();
    }

    protected function processTestOnlyErrors(array $expectedArgs, int $timeout = null)
    {
        $this->processBuilder->setArguments($expectedArgs)->willReturn($this->processBuilder);
        $this->processBuilder->setTimeout($timeout)->willReturn($this->processBuilder);

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);
            $callback(Process::ERR, 'bad output');
        });

        $this->output->writeln('ERR > bad output')->shouldBeCalled();
    }

    protected function processTestNoErrors(array $expectedArgs, int $timeout = null)
    {
        $this->processBuilder->setArguments($expectedArgs)->willReturn($this->processBuilder);
        $this->processBuilder->setTimeout($timeout)->willReturn($this->processBuilder);

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);
            $callback(Process::OUT, 'good output');
        });

        $this->output->writeln('good output')->shouldBeCalled();
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
