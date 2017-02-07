<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Restart;
use Jh\Workflow\Command\Stop;
use Jh\Workflow\Command\Up;
use Jh\Workflow\Command\Watch;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class RestartTest extends AbstractTestCommand
{
    /**
     * @var Restart
     */
    private $command;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    /**
     * @var ObjectProphecy|Up
     */
    private $upCommand;

    /**
     * @var ObjectProphecy|Stop
     */
    private $stopCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command      = new Restart();
        $this->application  = $this->prophesize(Application::class);
        $this->upCommand    = $this->prophesize(Up::class);
        $this->stopCommand  = $this->prophesize(Stop::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('up')->willReturn($this->upCommand->reveal());
        $this->application->find('stop')->willReturn($this->stopCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('restart', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Restarts the containers', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testCommandRunsAllSubCommands()
    {
        $this->application->find('stop')->shouldBeCalled();
        $this->application->find('up')->shouldBeCalled();

        $this->stopCommand->run($this->input, $this->output)->shouldBeCalled();
        $this->upCommand->run($this->input, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
