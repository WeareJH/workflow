<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\DatabaseDump;


/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DatabaseDumpTest extends AbstractTestCommand
{
    /**
     * @var DatabaseDump
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new DatabaseDump($this->commandLine->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('db-dump', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Dump the database to the host', $this->command->getDescription());
    }

    public function testHasDatabaseOptionAndValueIsRequired()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('database'));
        static::assertTrue($definition->getOption('database')->isValueRequired());
    }

    public function testDump()
    {
        $this->useValidEnvironment();

        $this->input->getOption('database')->willReturn(null);

        $this->commandLine->runQuietly('docker exec -i m2-db mysqldump -uroot -pdocker docker > dump.sql')
            ->shouldBeCalled();

        $this->output->writeln('<info>Database dump saved to ./dump.sql</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testDumpWithCustomDatabase()
    {
        $this->useValidEnvironment();

        $this->input->getOption('database')->willReturn('custom_db');

        $this->commandLine->runQuietly('docker exec -i m2-db mysqldump -uroot -pdocker custom_db > dump.sql')
            ->shouldBeCalled();
        $this->output->writeln('<info>Database dump saved to ./dump.sql</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
