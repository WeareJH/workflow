<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Ssh;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class SshTest extends AbstractTestCommand
{
    /**
     * @var Ssh
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Ssh($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('ssh', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Open up bash into the app container', $this->command->getDescription());
        static::assertArrayHasKey('root', $this->command->getDefinition()->getOptions());
    }

    public function testSshDefaultsToCorrectUser()
    {
        $this->useValidEnvironment();
        $this->input->getOption('root')->willReturn(false);
        $this->input->getOption('container')->willReturn(false);

        $this->commandLine->runQuietly('tput cols')->willReturn(100);
        $this->commandLine->runQuietly('tput lines')->willReturn(100);

        $expected = <<<CMD
docker exec \
    -it \
    -u "www-data" \
    -e COLUMNS="100" \
    -e LINES="100" \
    "m2-php" bash
CMD;

        $this->commandLine->runInteractively($expected)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSshWithRoot()
    {
        $this->useValidEnvironment();
        $this->input->getOption('root')->willReturn(true);
        $this->input->getOption('container')->willReturn(false);

        $this->commandLine->runQuietly('tput cols')->willReturn(100);
        $this->commandLine->runQuietly('tput lines')->willReturn(100);

        $expected = <<<CMD
docker exec \
    -it \
    -u "root" \
    -e COLUMNS="100" \
    -e LINES="100" \
    "m2-php" bash
CMD;

        $this->commandLine->runInteractively($expected)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSshWithCustomContainerName()
    {
        $this->useValidEnvironment();
        $this->input->getOption('root')->willReturn(false);
        $this->input->getOption('container')->willReturn('db');

        $this->commandLine->runQuietly('tput cols')->willReturn(100);
        $this->commandLine->runQuietly('tput lines')->willReturn(100);

        $expected = <<<CMD
docker exec \
    -it \
    -u "www-data" \
    -e COLUMNS="100" \
    -e LINES="100" \
    "m2-db" bash
CMD;

        $this->commandLine->runInteractively($expected)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSshWithCustomContainerNameAndRoot()
    {
        $this->useValidEnvironment();
        $this->input->getOption('root')->willReturn(true);
        $this->input->getOption('container')->willReturn('db');

        $this->commandLine->runQuietly('tput cols')->willReturn(100);
        $this->commandLine->runQuietly('tput lines')->willReturn(100);

        $expected = <<<CMD
docker exec \
    -it \
    -u "root" \
    -e COLUMNS="100" \
    -e LINES="100" \
    "m2-db" bash
CMD;

        $this->commandLine->runInteractively($expected)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
