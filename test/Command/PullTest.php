<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Pull;
use Jh\Workflow\Files;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class PullTest extends AbstractTestCommand
{
    /**
     * @var Pull
     */
    private $command;

    /**
     * @var Files
     */
    private $files;

    public function setUp()
    {
        parent::setUp();
        $this->files = $this->prophesize(Files::class);
        $this->command = new Pull($this->files->reveal());
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
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(true);

        $this->files->existsInContainer('m2-php', 'some-file.txt')->willReturn(true)->shouldBeCalled();
        $this->files->download('m2-php', ['some-file.txt'])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPullCommandDoesNotRemoveLocalFileIfItExistsAlready()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(false);

        $this->files->existsInContainer('m2-php', 'some-file.txt')->willReturn(true)->shouldBeCalled();
        $this->files->deleteLocally(['some-file.txt'])->shouldNotBeCalled();
        $this->files->download('m2-php', ['some-file.txt'])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPullCommandRemovesLocalFolderIfItExistsAlready()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-folder']);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(false);

        $this->files->existsInContainer('m2-php', 'some-folder')->willReturn(true)->shouldBeCalled();
        $this->files->deleteLocally(['some-folder'])->shouldBeCalled();
        $this->files->download('m2-php', ['some-folder'])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testOutputWhenFileDoesNotExistInContainer()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(true);

        $this->files->existsInContainer('m2-php', 'some-file.txt')->willReturn(false)->shouldBeCalled();

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
