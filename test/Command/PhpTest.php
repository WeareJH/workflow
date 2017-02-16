<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Php;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class PhpTest extends AbstractTestCommand
{
    /**
     * @var Ssh
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Php($this->processFactory->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('php', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Run a php script on the app container', $this->command->getDescription());
        static::assertArrayHasKey('php-file', $this->command->getDefinition()->getArguments());
    }

    public function testPhpCommand()
    {
        $this->useValidEnvironment();
        $this->input->getArgument('php-file')->willReturn('my-file.php');

        $expected = 'docker exec -it -u www-data m2-php php my-file.php';

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
