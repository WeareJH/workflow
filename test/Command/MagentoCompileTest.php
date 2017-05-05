<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\MagentoCompile;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class MagentoCompileTest extends AbstractTestCommand
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

        $this->command     = new MagentoCompile($this->processFactory->reveal());
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
        $description = 'Runs the magento DI compile command and pulls back required files to the host';

        static::assertEquals('magento-compile', $this->command->getName());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testComposerInstallCommand()
    {
        $this->useValidEnvironment();

        $cmd = 'docker exec -u www-data m2-php bin/magento setup:di:compile --ansi';
        $this->processTest($cmd);

        $expectedInput = new ArrayInput(['files' => ['var/di', 'var/generation']]);
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
