<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\MagentoConfigure;
use Jh\Workflow\Command\MagentoFullInstall;
use Jh\Workflow\Command\MagentoInstall;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoFullInstallTest extends AbstractTestCommand
{
    /**
     * @var MagentoFullInstall
     */
    private $command;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    /**
     * @var ObjectProphecy|MagentoInstall
     */
    private $installCommand;

    /**
     * @var ObjectProphecy|MagentoConfigure
     */
    private $configureCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command          = new MagentoFullInstall();
        $this->application      = $this->prophesize(Application::class);
        $this->installCommand   = $this->prophesize(MagentoInstall::class);
        $this->configureCommand = $this->prophesize(MagentoConfigure::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('magento-install')->willReturn($this->installCommand->reveal());
        $this->application->find('magento-configure')->willReturn($this->configureCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('magento-full-install', $this->command->getName());
        static::assertEquals(['mfi'], $this->command->getAliases());
        static::assertEquals('Runs magento-install and magento-configure commands', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testCommandRunsBothSubCommands()
    {
        $this->application->find('magento-install')->shouldBeCalled();
        $this->application->find('magento-configure')->shouldBeCalled();

        $this->installCommand->run($this->input, $this->output)->shouldBeCalled();
        $this->configureCommand->run($this->input, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
