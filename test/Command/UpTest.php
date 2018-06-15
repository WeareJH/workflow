<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Application;
use Jh\Workflow\Command\Pull;
use Jh\Workflow\Command\Up;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class UpTest extends AbstractTestCommand
{
    /**
     * @var Up
     */
    private $command;

    /**
     * @var Pull
     */
    private $pullCommand;

    /**
     * @var Application
     */
    private $application;

    public function setUp()
    {
        parent::setUp();

        $this->application  = $this->prophesize(Application::class);
        $this->command     = new Up($this->commandLine->reveal());
        $this->pullCommand = $this->prophesize(Pull::class);

        $this->application->find('pull')->willReturn($this->pullCommand->reveal());
        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->command->setApplication($this->application->reveal());
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('up', $this->command->getName());
        static::assertEquals(['start'], $this->command->getAliases());
        static::assertEquals('Uses docker-compose to start the containers', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
        static::assertArrayHasKey('no-build', $this->command->getDefinition()->getOptions());
    }

    public function testDevelopmentMode()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('no-build')->willReturn(false);

        $this->application->find('pull')->shouldBeCalled();

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build')
            ->shouldBeCalled();

        $expectedPullInput = new ArrayInput(['files' => ['.docker/composer-cache']]);
        $this->pullCommand->run($expectedPullInput, $this->output)->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testProductionMode()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-build')->willReturn(false);

        $this->application->find('pull')->shouldBeCalled();

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build')
            ->shouldBeCalled();

        $expectedPullInput = new ArrayInput(['files' => ['.docker/composer-cache']]);
        $this->pullCommand->run($expectedPullInput, $this->output)->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testWithNoBuild()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-build')->willReturn(true);

        $this->application->find('pull')->shouldBeCalled();

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d')
            ->shouldBeCalled();

        $expectedPullInput = new ArrayInput(['files' => ['.docker/composer-cache']]);
        $this->pullCommand->run($expectedPullInput, $this->output)->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
