<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Delete;
use Jh\Workflow\Files;
use Symfony\Component\Console\Input\InputArgument;

class DeleteTest extends AbstractTestCommand
{
    /**
     * @var Delete
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
        $this->command = new Delete($this->files->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('delete', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Delete files from the container', $this->command->getDescription());
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

    public function testDeleteCommandRelativePath()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalled()->willReturn(['some-file.txt']);

        $this->files->delete('m2-php', ['some-file.txt'])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testDeleteCommandAbsolutePath()
    {
        $this->useValidEnvironment();

        $filePath = realpath('some-file.txt');
        $this->input->getArgument('files')->shouldBeCalled()->willReturn([$filePath]);

        $this->files->delete('m2-php', [$filePath])->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
