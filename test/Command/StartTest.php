<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Build;
use Jh\Workflow\Command\Start;
use Jh\Workflow\Command\Up;
use Jh\Workflow\Command\Watch;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class StartTest extends AbstractTestCommand
{
    /**
     * @var Start
     */
    private $command;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    /**
     * @var ObjectProphecy|Watch
     */
    private $watchCommand;

    /**
     * @var ObjectProphecy|Up
     */
    private $upCommand;

    /**
     * @var ObjectProphecy|Build
     */
    private $buildCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command      = new Start();
        $this->application  = $this->prophesize(Application::class);
        $this->buildCommand = $this->prophesize(Build::class);
        $this->upCommand    = $this->prophesize(Up::class);
        $this->watchCommand = $this->prophesize(Watch::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('build')->willReturn($this->buildCommand->reveal());
        $this->application->find('up')->willReturn($this->upCommand->reveal());
        $this->application->find('watch')->willReturn($this->watchCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('start', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Runs build, up and watch comands', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testCommandRunsAllSubCommands()
    {
        $this->application->find('build')->shouldBeCalled();
        $this->application->find('up')->shouldBeCalled();
        $this->application->find('watch')->shouldBeCalled();

        $this->buildCommand->run($this->input, $this->output)->shouldBeCalled();
        $this->upCommand->run($this->input, $this->output)->shouldBeCalled();
        $this->watchCommand->run($this->input, $this->output)->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
