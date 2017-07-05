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
        $this->command = new DatabaseDump($this->processFactory->reveal());
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

        $this->processTestNoOutput('docker exec -i m2-db mysqldump -udocker -pdocker docker > dump.sql');
        $this->output->writeln('<info>Database dump saved to ./dump.sql</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testDumpWithCustomDatabase()
    {
        $this->useValidEnvironment();

        $this->input->getOption('database')->willReturn('custom_db');

        $this->processTestNoOutput('docker exec -i m2-db mysqldump -udocker -pdocker custom_db > dump.sql');
        $this->output->writeln('<info>Database dump saved to ./dump.sql</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
