<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\CommandLine;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use React\EventLoop\StreamSelectLoop;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\BufferingLogger;

class CommandLineTest extends TestCase
{
    /**
     * @var CommandLine
     */
    private $commandLine;

    /**
     * @var StreamSelectLoop
     */
    private $loop;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp()
    {
        $this->loop = new StreamSelectLoop;
        $this->output = new BufferedOutput;
        $this->commandLine = new CommandLine($this->loop, new NullLogger, $this->output);
    }

    /**
     * @expectedException \Jh\Workflow\ProcessFailedException
     */
    public function testRunThrowsExceptionIfCommandFails()
    {
        $this->commandLine->run('exit 1');
    }

    public function testRunOutputsAndReturnsCommandOutput()
    {
        $out = $this->commandLine->run('echo "yes"');

        self::assertEquals("yes\n", $out);
        self::assertEquals("yes\n", $this->output->fetch());
    }

    public function testRunQuietlyDoesNotOutputButReturnsOutput()
    {
        $out = $this->commandLine->runQuietly('echo "yes"');

        self::assertEquals("yes\n", $out);
        self::assertEquals('', $this->output->fetch());
    }

    public function testRunAsyncOutputsAndExecutesCallBackAfterCommand()
    {
        $ran = false;
        $this->commandLine->runAsync('echo "yes"', function () use (&$ran) {
            $ran = true;
        });

        $this->loop->run();

        self::assertEquals("yes\n", $this->output->fetch());
        self::assertTrue($ran);
    }

    public function testRunLogsCommand()
    {
        $this->commandLine = new CommandLine($this->loop, $log = new BufferingLogger, $this->output);

        $this->commandLine->run('echo "yes"');

        self::assertEquals([['debug', 'Running command [normal]: "echo "yes""', []]], $log->cleanLogs());
    }

    public function testRunQuietlyLogsCommand()
    {
        $this->commandLine = new CommandLine($this->loop, $log = new BufferingLogger, $this->output);

        $this->commandLine->runQuietly('echo "yes"');

        self::assertEquals([['debug', 'Running command [quiet]: "echo "yes""', []]], $log->cleanLogs());
    }

    public function testRunAsyncLogsCommand()
    {
        $this->commandLine = new CommandLine($this->loop, $log = new BufferingLogger, $this->output);

        $this->commandLine->runAsync('echo "yes"');

        self::assertEquals([['debug', 'Running command [async]: "echo "yes""', []]], $log->cleanLogs());
    }
}
