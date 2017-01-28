<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Magento;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoTest extends AbstractTestCommand
{
    /**
     * @var Magento
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Magento($this->processBuilder->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Works as a proxy to the Magento bin inside the container';

        static::assertEquals('magento', $this->command->getName());
        static::assertEquals(['mage', 'm'], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
        static::assertArrayHasKey('cmd', $this->command->getDefinition()->getArguments());
    }

    /**
     * @dataProvider magentoCommandProvidor
     */
    public function testCommandWorksAsAProxy($args)
    {
        $this->useValidEnvironment();

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = array_merge(['workflow', 'magento'], $args);

        $this->processTest(array_merge([
            'docker',
            'exec',
            '-u',
            'www-data',
            'm2-php',
            'bin/magento'
        ], $args));

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function magentoCommandProvidor() : array
    {
        return [
            [['cache:flush', 'config']],
            [['setup:static-content:deploy', '--theme="Luma/default"']],
            [['module:status']],
            [['module:disable', 'Magento_Weee']]
        ];
    }

    public function testExceptionThrownIfNoCommandDefined()
    {
        $this->useValidEnvironment();
        $this->expectException(\RuntimeException::class);

        // We have to use $_SERVER['argv'] here
        $_SERVER['argv'] = ['workflow', 'magento'];

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
