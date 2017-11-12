<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Build;
use Jh\Workflow\Command\Pull;
use Jh\Workflow\Command\Start;
use Jh\Workflow\Command\Up;
use Jh\Workflow\Command\Watch;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

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
     * @var ObjectProphecy|Pull
     */
    private $pullCommand;

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
        $this->pullCommand  = $this->prophesize(Pull::class);
        $this->watchCommand = $this->prophesize(Watch::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('up')->willReturn($this->upCommand->reveal());
        $this->application->find('pull')->willReturn($this->pullCommand->reveal());
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
        $this->application->find('up')->shouldBeCalled();
        $this->application->find('pull')->shouldBeCalled();
        $this->application->find('watch')->shouldBeCalled();

        $expectedPullInput = new ArrayInput(['files' => ['.docker/composer-cache']]);
        $expectedWatchInut = new ArrayInput([]);

        $this->upCommand->run($this->input, $this->output)->shouldBeCalled();
        $this->pullCommand->run($expectedPullInput, $this->output)->shouldBeCalled();
        $this->watchCommand->run($expectedWatchInut, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
