<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\MagentoInstall;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstallTest extends AbstractTestCommand
{
    /**
     * @var MagentoInstall
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

        $this->command     = new MagentoInstall($this->processBuilder->reveal());
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
        static::assertEquals('magento-install', $this->command->getName());
        static::assertEquals(['mi'], $this->command->getAliases());
        static::assertEquals('Runs the magento install script', $this->command->getDescription());
    }

    public function testMagentoInstallCommand()
    {
        $this->useValidEnvironment();

        $expectedArgs = [
            'docker exec',
            'm2-php',
            'magento-install'
        ];

        $this->processTest($expectedArgs);

        $expectedInput = new ArrayInput(['files' => ['app/etc']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();
        $this->output->writeln('Install complete!')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
