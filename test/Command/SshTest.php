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
        $this->command = new Ssh($this->processFactory->reveal());
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

        $expected = 'docker exec -it -u www-data m2-php bash';

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

    public function testBuildForProduction()
    {
        $this->useValidEnvironment();
        $this->input->getOption('root')->willReturn(true);

        $expected = 'docker exec -it -u root m2-php bash';

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
