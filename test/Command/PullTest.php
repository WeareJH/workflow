<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Pull;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class PullTest extends AbstractTestCommand
{
    /**
     * @var Pull
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Pull($this->processFactory->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $expectedHelp  = "Pull files from the docker environment to the host, Useful for pulling vendor etc\n\n";
        $expectedHelp .= 'If the watch is running and you pull a file that is being watched it will ';
        $expectedHelp .= "automatically be pushed back into the container\n";
        $expectedHelp .= 'If this is not what you want (large dirs can cause issues here) stop the watch, ';
        $expectedHelp .= 'pull then start the watch again afterwards';

        static::assertEquals('pull', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Pull files from the docker environment to the host', $this->command->getDescription());
        static::assertArrayHasKey('files', $this->command->getDefinition()->getArguments());
        static::assertEquals($expectedHelp, $this->command->getHelp());
    }

    public function testFilesArgumentIsRequiredAndArray()
    {
        $args = $this->command->getDefinition()->getArguments();
        /** @var InputArgument $fileArg */
        $fileArg = array_shift($args);

        static::assertTrue($fileArg->isRequired());
        static::assertTrue($fileArg->isArray());
    }

    public function testPullCommand()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);

        $this->processTestNoOutput(
            "docker exec m2-php php -r \"echo file_exists('/var/www/some-file.txt') ? 'true' : 'false';\""
        );
        $this->process->getOutput()->willReturn('true');

        $this->processTest('docker cp m2-php:/var/www/some-file.txt ./');
        $this->output
            ->writeln("<info>Copied 'some-file.txt' from container into './' on the host</info>")
            ->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testOutputWhenFileDoesntExistInContainer()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);

        $this->processTestNoOutput(
            "docker exec m2-php php -r \"echo file_exists('/var/www/some-file.txt') ? 'true' : 'false';\""
        );
        $this->process->getOutput()->willReturn('false');

        $this->output->writeln('Looks like "some-file.txt" doesn\'t exist')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
