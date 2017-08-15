<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Exec;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ExecTest extends AbstractTestCommand
{
    /**
     * @var Exec
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Exec($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('exec', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Run an arbitrary command on the app container', $this->command->getDescription());
        static::assertArrayHasKey('command-line', $this->command->getDefinition()->getArguments());
    }

    public function testExecCommand()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', 'ls', '-la'];

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php ls -la')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecAsRootWithShortOption()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', '-r', 'ls', '-la'];

        $this->commandLine->runInteractively('docker exec -it -u root m2-php ls -la')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecAsRootWithLongOption()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', '--root', 'ls', '-la'];

        $this->commandLine->runInteractively('docker exec -it -u root m2-php ls -la')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecWithRootShortOptionPassedAfterCommandIsInterpretedAsCommand()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', 'ls', '-la', '-r'];

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php ls -la -r')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecWithRootlongOptionPassedAfterCommandIsInterpretedAsCommand()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', 'ls', '-la', '--root'];

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php ls -la --root')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
