<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Build;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class BuildTest extends AbstractTestCommand
{
    /**
     * @var Build
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Build($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('build', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Runs docker build to create an image ready for use', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testBuildForDevelopment()
    {
        $this->useValidEnvironment();

        $expected = 'docker-compose -f docker-compose.yml -f docker-compose.dev.yml build';

        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('no-cache')->willReturn(false);

        $this->commandLine->run($expected)->shouldBeCalled();
        $this->output->writeln('<info>Build complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testBuildForDevelopmentWithNoCache()
    {
        $this->useValidEnvironment();

        $expected = 'docker-compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache';

        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('no-cache')->willReturn(true);

        $this->commandLine->run($expected)->shouldBeCalled();
        $this->output->writeln('<info>Build complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testBuildForProduction()
    {
        $this->useValidEnvironment();

        $expected = 'docker-compose -f docker-compose.yml -f docker-compose.prod.yml build';

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-cache')->willReturn(false);

        $this->commandLine->run($expected)->shouldBeCalled();
        $this->output->writeln('<info>Build complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testBuildForProductionWithNoCache()
    {
        $this->useValidEnvironment();

        $expected = 'docker-compose -f docker-compose.yml -f docker-compose.prod.yml build --no-cache';

        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('no-cache')->willReturn(true);

        $this->commandLine->run($expected)->shouldBeCalled();
        $this->output->writeln('<info>Build complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
