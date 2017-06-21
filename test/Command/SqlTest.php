<?php

namespace Jh\WorkflowTest\Command;
use Jh\Workflow\Command\Sql;


/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class SqlTest extends AbstractTestCommand
{
    /**
     * @var Sql
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Sql($this->processFactory->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        static::assertEquals('sql', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals('Run arbitary sql against the database', $this->command->getDescription());
    }

    public function testHasSqlOptionAndIsOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('sql'));
        static::assertTrue($definition->getOption('sql')->isValueOptional());
    }

    public function testHasFileOptionAndIsOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('file'));
        static::assertTrue($definition->getOption('file')->isValueOptional());
    }

    public function testHasDatabaseOptionAndValueIsRequired()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('database'));
        static::assertTrue($definition->getOption('database')->isValueRequired());
    }

    public function testRawSqlIsRun()
    {
        $this->useValidEnvironment();

        $this->input->getOption('sql')->willReturn('SELECT * FROM core_config_data');
        $this->input->getOption('file')->willReturn(null);
        $this->input->getOption('database')->willReturn(null);

        $this->processTest(
            'docker exec -t m2-db mysql -udocker -pdocker docker -e "SELECT * FROM core_config_data"'
        );
        
        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSqlFileIsRun()
    {
        $this->useValidEnvironment();

        $this->input->getOption('sql')->willReturn(null);
        $this->input->getOption('file')->willReturn('some-import.sql');
        $this->input->getOption('database')->willReturn(null);

        $this->processTest('docker exec -i m2-db mysql -udocker -pdocker docker < some-import.sql');
        $this->output->writeln('<info>DB import complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSqlFileIsRunWithCustomDatabase()
    {
        $this->useValidEnvironment();

        $this->input->getOption('sql')->willReturn(null);
        $this->input->getOption('file')->willReturn('some-import.sql');
        $this->input->getOption('database')->willReturn('custom_db');

        $this->processTest('docker exec -i m2-db mysql -udocker -pdocker custom_db < some-import.sql');
        $this->output->writeln('<info>DB import complete!</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionIsThrownIfSqlFileDoesntExist()
    {
        $this->useInvalidEnvironment();

        $this->input->getOption('sql')->willReturn(null);
        $this->input->getOption('file')->willReturn('some-import.sql');
        $this->input->getOption('database')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
