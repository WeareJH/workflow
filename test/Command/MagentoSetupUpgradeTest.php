<?php

declare(strict_types=1);

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\MagentoSetupUpgrade;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Diego Cabrejas <diego@wearejh.com>
 */
class MagentoSetupUpgradeTest extends AbstractTestCommand
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

        $this->command     = new MagentoSetupUpgrade($this->commandLine->reveal());
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
        $description = 'Upgrades the Magento application and updates the config.php file';

        static::assertEquals('setup:upgrade', $this->command->getName());
        static::assertEquals($description, $this->command->getDescription());
        static::assertTrue($this->command->getDefinition()->hasOption(MagentoSetupUpgrade::INPUT_KEY_KEEP_GENERATED));
    }

    public function testSetupUpgradeCommandWithoutOption()
    {
        $this->useValidEnvironment();
        $this->input
            ->getOption(MagentoSetupUpgrade::INPUT_KEY_KEEP_GENERATED)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $cmd = 'docker exec -u www-data m2-php bin/magento setup:upgrade  --ansi';
        $this->commandLine->run($cmd)->shouldBeCalled();

        $expectedInput = new ArrayInput(['files' => ['app/etc/config.php']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSetupUpgradeCommandWithOption()
    {
        $this->useValidEnvironment();
        $this->input
            ->getOption(MagentoSetupUpgrade::INPUT_KEY_KEEP_GENERATED)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $cmd = 'docker exec -u www-data m2-php bin/magento setup:upgrade --keep-generated --ansi';
        $this->commandLine->run($cmd)->shouldBeCalled();

        $expectedInput = new ArrayInput(['files' => ['app/etc/config.php']]);
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