<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\MagentoConfigure;
use Jh\Workflow\Command\Pull;
use Jh\Workflow\Command\Sql;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoConfigureTest extends AbstractTestCommand
{
    /**
     * @var MagentoConfigure
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

    /**
     * @var ObjectProphecy|Sql
     */
    private $sqlCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command     = new MagentoConfigure($this->processFactory->reveal());
        $this->application = $this->prophesize(Application::class);
        $this->pullCommand = $this->prophesize(Pull::class);
        $this->sqlCommand  = $this->prophesize(Sql::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('pull')->willReturn($this->pullCommand->reveal());
        $this->application->find('sql')->willReturn($this->sqlCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('magento-configure', $this->command->getName());
        static::assertEquals(['mc'], $this->command->getAliases());
        static::assertEquals('Configures Magento ready for Docker use', $this->command->getDescription());
        static::assertArrayHasKey('prod', $this->command->getDefinition()->getOptions());
    }

    public function testMagentoConfigureCommandForDevelopment()
    {
        $this->useValidEnvironment();

        $this->processTest('docker exec m2-php magento-configure');

        $expectedInput = new ArrayInput(['files' => ['app/etc/env.php']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $expectedSql =  "DELETE FROM core_config_data WHERE path LIKE 'system/smtp/%'; ";
        $expectedSql .= "INSERT INTO core_config_data (scope, scope_id, path, value) ";
        $expectedSql .= "VALUES ";
        $expectedSql .= "('default', 0, 'system/smtp/host', 'm2-mail'), ";
        $expectedSql .= "('default', 0, 'system/smtp/port', '1025');";

        $expectedInput = new ArrayInput(['--sql' => $expectedSql]);
        $this->sqlCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->output->writeln('Configuration complete!')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testMagentoConfigureCommandForProduction()
    {
        $this->useValidEnvironment();

        $this->input->getOption('prod')->willReturn(true);

        $this->processTest('docker exec m2-php magento-configure -p');

        $expectedInput = new ArrayInput(['files' => ['app/etc/env.php']]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();

        $this->output->writeln('Configuration complete!')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
