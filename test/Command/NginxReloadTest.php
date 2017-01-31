<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\NginxReload;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class NginxReloadTest extends AbstractTestCommand
{
    /**
     * @var NginxReload
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new NginxReload($this->processFactory->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('nginx-reload', $this->command->getName());
        static::assertEquals(['nginx'], $this->command->getAliases());
        static::assertEquals('Sends reload signal to NGINX in the container', $this->command->getDescription());
    }

    public function testNginxReloadCommand()
    {
        $this->useValidEnvironment();

        $this->processTest('docker exec m2 nginx -s "reload"');
        $this->output->writeln('Reload signal sent')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
