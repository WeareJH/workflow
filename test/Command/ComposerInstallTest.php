<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\ComposerInstall;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerInstallTest extends AbstractTestCommand
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

        $this->command     = new ComposerInstall($this->processFactory->reveal());
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
        $description = 'Runs composer install inside the container and pulls back required files to the host';

        static::assertEquals('composer-install', $this->command->getName());
        static::assertEquals(['ci'], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testComposerInstallCommand()
    {
        $this->useValidEnvironment();

        $this->output->getVerbosity()->willReturn(OutputInterface::OUTPUT_NORMAL);

        $cmd = 'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache m2-php composer install -o --ansi';
        $this->processTest($cmd);

        $expectedInput = new ArrayInput(['files' => ['vendor']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    /**
     * @param $verbosity
     * @param $expectedFlag
     * @dataProvider composerInstallVerbosityProvider
     */
    public function testComposerInstallPassesVerbosityCorrectly($verbosity, $expectedFlag)
    {
        $this->useValidEnvironment();

        $this->output->getVerbosity()->willReturn($verbosity);

        $cmd = sprintf(
            'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache m2-php composer install -o --ansi %s',
            $expectedFlag
        );
        $this->processTest($cmd);

        $expectedInput = new ArrayInput(['files' => ['vendor', '.docker/composer-cache']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function composerInstallVerbosityProvider()
    {
        return [
            [OutputInterface::VERBOSITY_VERBOSE, '-v'],
            [OutputInterface::VERBOSITY_VERY_VERBOSE, '-vv'],
            [OutputInterface::VERBOSITY_DEBUG, '-vvv'],
        ];
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
