<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Exec;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

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
        $this->command = new Exec($this->processFactory->reveal());
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

        $expected = 'docker exec -it -u www-data m2-php ls -la';

        $this->processFactory->create($expected)->willReturn($this->process->reveal());
        $this->process->setTty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecAsRootWithShortOption()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', '-r', 'ls', '-la'];

        $expected = 'docker exec -it -u root m2-php ls -la';

        $this->processFactory->create($expected)->willReturn($this->process->reveal());
        $this->process->setTty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecAsRootWithLongOption()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', '--root', 'ls', '-la'];

        $expected = 'docker exec -it -u root m2-php ls -la';

        $this->processFactory->create($expected)->willReturn($this->process->reveal());
        $this->process->setTty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecWithRootShortOptionPassedAfterCommandIsInterpretedAsCommand()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', 'ls', '-la', '-r'];

        $expected = 'docker exec -it -u www-data m2-php ls -la -r';

        $this->processFactory->create($expected)->willReturn($this->process->reveal());
        $this->process->setTty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExecWithRootlongOptionPassedAfterCommandIsInterpretedAsCommand()
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'exec', 'ls', '-la', '--root'];

        $expected = 'docker exec -it -u www-data m2-php ls -la --root';

        $this->processFactory->create($expected)->willReturn($this->process->reveal());
        $this->process->setTty(true)->shouldBeCalled();

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);

            $callback(Process::ERR, 'bad output');
            $callback(Process::OUT, 'good output');
        });

        $this->output->write('bad output')->shouldBeCalled();
        $this->output->write('good output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
