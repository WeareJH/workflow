<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\ComposerUpdate;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerUpdateTest extends AbstractTestCommand
{
    /**
     * @var ComposerUpdate
     */
    private $command;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    /**
     * @var ObjectProphecy|Pull
     */
    private $pullCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command     = new ComposerUpdate($this->processBuilder->reveal());
        $this->application = $this->prophesize(Application::class);
        $this->pullCommand = $this->prophesize(Pull::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('pull')->willReturn($this->pullCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Runs composer update inside the container and pulls back required files to the host';

        static::assertEquals('composer-update', $this->command->getName());
        static::assertEquals(['cu'], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testComposerUpdateCommand()
    {
        $this->useValidEnvironment();

        $expectedArgs = [
            'docker exec',
            'm2-php',
            'composer update',
            '-o'
        ];

        $this->processTest($expectedArgs);

        $expectedInput = new ArrayInput(['files' => ['vendor', 'composer.lock']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
