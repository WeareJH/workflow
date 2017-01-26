<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\XdebugLoopback;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class XdebugLoopbackTest extends AbstractTestCommand
{
    /**
     * @var XdebugLoopback
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new XdebugLoopback($this->processBuilder->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Starts the network loopback to allow Xdebug from Docker';

        static::assertEquals('xdebug-loopback', $this->command->getName());
        static::assertEquals(['xdebug'], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testXdebugLoopbackCommand()
    {
        $expectedArgs = ['sudo', 'ifconfig', 'lo0', 'alias', '10.254.254.254'];

        $this->processBuilder->setArguments($expectedArgs)->willReturn($this->processBuilder);
        $this->processBuilder->setTimeout(null)->willReturn($this->processBuilder);
        $this->process->setPty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);
            $callback(Process::OUT, 'some output');
        });

        $this->output->writeln('some output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
