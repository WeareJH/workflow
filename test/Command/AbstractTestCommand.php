<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\CommandLine;
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
     * @var ObjectProphecy|CommandLine
     */
    protected $commandLine;

    public function setUp()
    {
        $this->prophet = new Prophet();

        $this->input  = $this->prophesize(ArgvInput::class);
        $this->output = $this->prophesize(Output::class);

        $this->commandLine = $this->prophesize(CommandLine::class);
    }

    protected function useInvalidEnvironment()
    {
        chdir(__DIR__ . '/../fixtures/invalid-env');
    }

    protected function useValidEnvironment()
    {
        chdir(__DIR__ . '/../fixtures/valid-env');
    }

    protected function useBrokenEnvironment()
    {
        chdir(__DIR__ . '/../fixtures/broken-env');
    }
}
