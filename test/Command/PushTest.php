<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Push;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class PushTest extends AbstractTestCommand
{
    /**
     * @var Push
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Push($this->processBuilder->reveal());
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

    public function testPushCommand()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalledTimes(2)->willReturn(['some-file.txt']);

        $expectedArgs = [
            'docker',
            'cp',
            'some-file.txt',
            'm2-php:/var/www/',
        ];

        $this->processTest($expectedArgs);
        $this->output->writeln("<info> + some-file.txt > m2-php </info>")->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testOutputWhenFileDoesntExistInContainer()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('files')->shouldBeCalledTimes(2)->willReturn(['some-bad-file.txt']);
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
