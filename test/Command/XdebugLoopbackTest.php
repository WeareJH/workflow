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
        $this->command = new XdebugLoopback($this->commandLine->reveal());
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
        $this->commandLine->run('sudo ifconfig lo0 alias 10.254.254.254')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
