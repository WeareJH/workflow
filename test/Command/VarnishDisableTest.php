<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\NginxReload;
use Jh\Workflow\Command\VarnishDisable;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class VarnishDisableTest extends AbstractTestCommand
{
    /**
     * @var NginxReload
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new VarnishDisable($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('varnish-disable', $this->command->getName());
        static::assertEquals(['vd'], $this->command->getAliases());
        static::assertEquals('Switches the VCL to be a proxy', $this->command->getDescription());
    }

    public function testVarnishEnableCommand()
    {
        $this->useValidEnvironment();

        $this->commandLine->run('docker exec -t m2-varnish varnishadm vcl.use boot')->shouldBeCalled();
        $this->output->writeln('<info>Varnish caching disabled</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
