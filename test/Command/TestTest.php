<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Test;
use Jh\Workflow\Command\XdebugLoopback;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class TestTest extends AbstractTestCommand
{
    /**
     * @var Test
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Test($this->processBuilder->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('test', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Run the projects test suite', $this->command->getDescription());
    }

    public function testCommandrunsExpectedTests()
    {
        $this->useValidEnvironment();

        $expectedArgs = [
            'docker exec',
            '-u www-data',
            'm2-php',
            'vendor/bin/phpcs',
            '-s app/code',
            '--standard=PSR2',
            '--warning-severity=0'
        ];

        $this->processBuilder->setArguments($expectedArgs)->willReturn($this->processBuilder);
        $this->processBuilder->setTimeout(null)->willReturn($this->processBuilder);

        $this->process->run(Argument::type('callable'))->will(function ($args) {
            $callback = array_shift($args);
            $callback(Process::OUT, 'some output');
        });

        $this->output->writeln('some output')->shouldBeCalled();
        $this->output->writeln('<info>Tests complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
