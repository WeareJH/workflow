<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\N98;
use Jh\Workflow\Files;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class N98Test extends AbstractTestCommand
{
    /**
     * @var Files
     */
    private $files;

    /**
     * @var Exec
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->files = $this->prophesize(Files::class);
        $this->command = new N98($this->commandLine->reveal(), $this->files->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('n98', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Run N98 commands in the PHP container - downloads N98 if not present', $this->command->getDescription());
        static::assertArrayHasKey('command-line', $this->command->getDefinition()->getArguments());
    }

    public function testN98CommandDownloadsN98IfItDoesNotExist()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('command-line')->shouldBeCalled()->willReturn('admin:user:list');

        $this->files->existsInContainer('m2-php', './n98-magerun2.phar')->willReturn(false)->shouldBeCalled();

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php curl -O https://files.magerun.net/n98-magerun2.phar')->shouldBeCalled();
        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php chmod +x ./n98-magerun2.phar')->shouldBeCalled();
        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php ./n98-magerun2.phar admin:user:list')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testN98CommandWhenN98ExistsAlready()
    {
        $this->useValidEnvironment();

        $this->input->getArgument('command-line')->shouldBeCalled()->willReturn('admin:user:list');

        $this->files->existsInContainer('m2-php', './n98-magerun2.phar')->willReturn(true)->shouldBeCalled();

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php curl -O https://files.magerun.net/n98-magerun2.phar')->shouldNotBeCalled();
        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php chmod +x ./n98-magerun2.phar')->shouldNotBeCalled();
        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php ./n98-magerun2.phar admin:user:list')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
