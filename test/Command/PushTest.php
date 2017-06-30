<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Push;
use Jh\Workflow\Files;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class PushTest extends AbstractTestCommand
{
    /**
     * @var Push
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
        $this->command = new Push($this->files->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('push', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Push files from host to the container', $this->command->getDescription());
        static::assertArrayHasKey('files', $this->command->getDefinition()->getArguments());
    }

    public function testFilesArgumentIsRequiredAndArray()
    {
        $args = $this->command->getDefinition()->getArguments();
        /** @var InputArgument $fileArg */
        $fileArg = array_shift($args);

        static::assertTrue($fileArg->isRequired());
        static::assertTrue($fileArg->isArray());
    }

    public function testPushCommandRelativePath()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(true);

        $this->files->upload('m2-php', ['some-file.txt'])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPushCommandAbsolutePath()
    {
        $this->useValidEnvironment();

        $filePath = realpath('some-file.txt');
        $this->input->getArgument('files')->shouldBeCalled()->willReturn([$filePath]);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(true);

        $this->files->upload('m2-php', [$filePath])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPushCommandDoesNotRemoveFileFirstIfItExistsAlready()
    {
        $this->useValidEnvironment();

        $filePath = realpath('some-file.txt');
        $this->input->getArgument('files')->shouldBeCalled()->willReturn([$filePath]);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(false);

        $this->files->delete('m2-php', [$filePath])->shouldNotBeCalled();
        $this->files->upload('m2-php', [$filePath])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPushCommandRemovesRemoteFolderIfItExistsAlready()
    {
        $this->useValidEnvironment();

        $filePath = realpath('some-folder');
        $this->input->getArgument('files')->shouldBeCalled()->willReturn([$filePath]);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(false);

        $this->files->existsInContainer('m2-php', $filePath)->willReturn(true)->shouldBeCalled();
        $this->files->delete('m2-php', [$filePath])->shouldBeCalled();
        $this->files->upload('m2-php', [$filePath])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testPushCommandDoesNotRemoveRemoteFolderIfItDoesNotExistsAlready()
    {
        $this->useValidEnvironment();

        $filePath = realpath('some-folder');
        $this->input->getArgument('files')->shouldBeCalled()->willReturn([$filePath]);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(false);

        $this->files->existsInContainer('m2-php', $filePath)->willReturn(false)->shouldBeCalled();
        $this->files->delete('m2-php', [$filePath])->shouldNotBeCalled();
        $this->files->upload('m2-php', [$filePath])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testOutputWhenFileDoesNotExistInContainer()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-bad-file.txt']);
        $this->input->getOption('no-overwrite')->shouldBeCalled()->willReturn(true);

        $this->output->writeln('Looks like "some-bad-file.txt" doesn\'t exist')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
