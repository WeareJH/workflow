<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Up;
use Symfony\Component\Yaml\Yaml;

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
    }

    public function testYamlConfigurationCreatedWithoutMountOption() {

        $mountOption = [];

        $yaml = Up::generateYaml([
            'docker-compose.yml',
            'docker-compose.dev.yml'
        ], $mountOption);

        $this->assertStringEqualsFile(
            __DIR__ . '/../fixtures/yaml/without-mount.yml',
            $yaml
        );
    }

    public function testYamlConfigurationCreatedWithMountOption() {

        $mountOption = ['./app/code', 'app/design'];

        $yaml = Up::generateYaml([
            'docker-compose.yml',
            'docker-compose.dev.yml'
        ], $mountOption);

        $this->assertStringEqualsFile(
            __DIR__ . '/../fixtures/yaml/with-multi-mount.yml',
            $yaml
        );
    }

    public function testUpRunsInDevelopmentMode()
    {
        $this->useValidEnvironment();

        $mountOption = [];
        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('mount')->willReturn($mountOption);

        $expectedYaml = Up::generateYaml([
            'docker-compose.yml',
            'docker-compose.dev.yml'
        ], $mountOption);

        $this->commandLine
            ->run(sprintf('echo "%s" | docker-compose -f - up -d', $expectedYaml))
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testUpRunsInProductionMode()
    {
        $this->useValidEnvironment();

        $mountOption = [];
        $this->input->getOption('prod')->willReturn(true);
        $this->input->getOption('mount')->willReturn($mountOption);

        $expectedYaml = Up::generateYaml([
            'docker-compose.yml',
            'docker-compose.prod.yml'
        ], $mountOption);

        $this->commandLine
            ->run(sprintf('echo "%s" | docker-compose -f - up -d', $expectedYaml))
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testUpRunsInDevelopmentModeWithSingleMountOption()
    {
        $this->useValidEnvironment();

        $mountOption = ['app/code'];
        $this->input->getOption('prod')->willReturn(false);
        $this->input->getOption('mount')->willReturn($mountOption);

        $expectedYaml = Up::generateYaml([
            'docker-compose.yml',
            'docker-compose.dev.yml'
        ], $mountOption);

        $this->commandLine
            ->run(sprintf('echo "%s" | docker-compose -f - up -d', $expectedYaml))
            ->shouldBeCalled();

        $this->output->writeln('<info>Containers started</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
