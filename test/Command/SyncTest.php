<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Push;
use Jh\Workflow\Command\Sync;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class SyncTest extends AbstractTestCommand
{
    /**
     * @var Sync
     */
    private $command;

    /**
     * @var ObjectProphecy|Push
     */
    private $pushCommand;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    public function setUp()
    {
        parent::setUp();
        $this->command     = new Sync($this->processBuilder->reveal());
        $this->application = $this->prophesize(Application::class);
        $this->pushCommand = $this->prophesize(Push::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('push')->willReturn($this->pushCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Syncs changes from the host filesystem to the relevant docker containers';

        static::assertEquals('sync', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testSyncWillAddAFileWhenItExists()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('file')->willReturn('some-file.txt');

        $expectedInput = new ArrayInput(['files' => ['some-file.txt']]);
        $this->pushCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSyncWillDeleteAFileWhenItDoesntExist()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('file')->willReturn('some-deleted-file.txt');

        $expectedArgs = [
            'docker',
            'exec',
            'm2-php',
            'rm',
            '-rf',
            '/var/www/some-deleted-file.txt',
        ];

        $this->processTestOnlyErrors($expectedArgs);
        $this->output->writeln('<fg=red> x some-deleted-file.txt > m2-php </fg=red>')->shouldBeCalled();
        $this->output->writeln('ERR > bad output')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
