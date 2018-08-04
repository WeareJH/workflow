<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\NginxReload;
use Jh\Workflow\Command\VarnishEnable;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class VarnishEnableTest extends AbstractTestCommand
{
    /**
     * @var NginxReload
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new VarnishEnable($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('varnish-enable', $this->command->getName());
        static::assertEquals(['ve'], $this->command->getAliases());
        static::assertEquals('Switches the VCL to use caching', $this->command->getDescription());
    }

    public function testVarnishEnableCommand()
    {
        $this->useValidEnvironment();

        $this->commandLine->run('docker-compose exec -T varnish varnishadm vcl.use boot0')->shouldBeCalled();
        $this->output->writeln('<info>Varnish caching enabled</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
