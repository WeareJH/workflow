<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Stop;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class StopTest extends AbstractTestCommand
{
    /**
     * @var Stop
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Stop($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('stop', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Stops the containers running', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testStopsDevelopmentMode()
    {
        $this->useValidEnvironment();

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.dev.yml stop')
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers stopped</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testStopsProductionMode()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);

        $this->commandLine
            ->run('docker-compose -f docker-compose.yml -f docker-compose.prod.yml stop')
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers stopped</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
