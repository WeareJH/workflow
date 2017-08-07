<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Php;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class PhpTest extends AbstractTestCommand
{
    /**
     * @var Ssh
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Php($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('php', $this->command->getName());
        static::assertEmpty($this->command->getAliases());
        static::assertEquals('Run a php script on the app container', $this->command->getDescription());
        static::assertArrayHasKey('php-file', $this->command->getDefinition()->getArguments());
    }

    public function testPhpCommand()
    {
        $this->useValidEnvironment();
        $this->input->getArgument('php-file')->willReturn('my-file.php');

        $this->commandLine->runInteractively('docker exec -it -u www-data m2-php php my-file.php')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionThrownIfComposeFileMissingImageTag()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
