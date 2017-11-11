<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Up;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class UpTest extends AbstractTestCommand
{
    /**
     * @var Up
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Up($this->commandLine->reveal());
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('up', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Uses docker-compose to start the containers', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
        static::assertArrayHasKey('no-build', $this->command->getDefinition()->getOptions());
    }

    public function testDevelopmentMode()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('no-build')->willReturn(false);

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build')
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testProductionMode()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-build')->willReturn(false);

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build')
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testWithNoBuild()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-build')->willReturn(true);

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d')
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
