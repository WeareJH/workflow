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

    public function testSqlArgumentIsOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasArgument('sql'));
        static::assertFalse($definition->getArgument('sql')->isRequired());
    }

    public function testFileOptionIsOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('file'));
        static::assertTrue($definition->getOption('file')->isValueOptional());
    }

    public function testRawSqlIsRun()
    {
        $this->useValidEnvironment();

        $this->input->hasArgument('sql')->willReturn(true);
        $this->input->getArgument('sql')->willReturn('SELECT * FROM core_config_data');
        $this->input->getOption('file')->willReturn(null);

        $this->processTest(
            'docker exec -t m2-db mysql -udocker -pdocker docker -e "SELECT * FROM core_config_data"'
        );
        
        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testSqlFileIsRun()
    {
        $this->useValidEnvironment();

        $this->input->hasArgument('sql')->willReturn(false);
        $this->input->getOption('file')->willReturn('some-import.sql');

        $this->processTest('docker cp some-import.sql m2-db:/root/some-import.sql');
        $this->processTest('docker exec m2-db mysql -udocker -pdocker docker < /root/some-import.sql');
        $this->processTest('docker exec m2-db rm /root/some-import.sql');

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionIsThrownIfSqlFileDoesntExist()
    {
        $this->useInvalidEnvironment();

        $this->input->hasArgument('sql')->willReturn(false);
        $this->input->getOption('file')->willReturn('some-import.sql');

        $this->expectException(\RuntimeException::class);
        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
