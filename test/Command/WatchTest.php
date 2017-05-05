<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Watch;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class WatchTest extends AbstractTestCommand
{
    /**
     * @var Watch
     */
    private $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new Watch($this->processFactory->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Keeps track of filesystem changes, piping the changes to the sync command';

        static::assertEquals('watch', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testWatchArgumentIsArrayAndOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasArgument('watches'));
        static::assertTrue($definition->getArgument('watches')->isArray());
        static::assertFalse($definition->getArgument('watches')->isRequired());
    }

    public function testNoDefaultsOptionIsSetAndTakesNoValue()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('no-defaults'));
        static::assertFalse($definition->getOption('no-defaults')->acceptValue());
    }

    public function testWatchWithDefaultValues()
    {
        $this->input->getArgument('watches')->willReturn([]);
        $this->input->getOption('no-defaults')->willReturn(false);

        $includes = 'app/code app/design composer.json phpcs.xml phpunit.xml';
        $excludes = '-e ".*__jb_.*$" -e ".*swp$" -e ".*swpx$"';
        $expected  = sprintf(
            'fswatch -r %s %s | xargs -n1 -I {} %s sync --ansi {}',
            $includes,
            $excludes,
            realpath(__DIR__ . '/../../bin/workflow')
        );
        
        $this->processTest($expected);
        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testWatchWithDefaultValuesAndDefinedWatches()
    {
        $this->input->getArgument('watches')->willReturn(['custom-dir']);
        $this->input->getOption('no-defaults')->willReturn(false);

        $includes = 'custom-dir app/code app/design composer.json phpcs.xml phpunit.xml';
        $excludes = '-e ".*__jb_.*$" -e ".*swp$" -e ".*swpx$"';
        $expected  = sprintf(
            'fswatch -r %s %s | xargs -n1 -I {} %s sync --ansi {}',
            $includes,
            $excludes,
            realpath(__DIR__ . '/../../bin/workflow')
        );

        $this->processTest($expected);
        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testNoDefaultsOptionRemovesDefaults()
    {
        $this->input->getArgument('watches')->willReturn(['custom-dir']);
        $this->input->getOption('no-defaults')->willReturn(true);

        $includes = 'custom-dir';
        $excludes = '-e ".*__jb_.*$" -e ".*swp$" -e ".*swpx$"';
        $expected  = sprintf(
            'fswatch -r %s %s | xargs -n1 -I {} %s sync --ansi {}',
            $includes,
            $excludes,
            realpath(__DIR__ . '/../../bin/workflow')
        );

        $this->processTest($expected);
        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionIsThrownWhenNoDefaultsSetAndNoArgumentsPassed()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->input->getArgument('watches')->willReturn([]);
        $this->input->getOption('no-defaults')->willReturn(true);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
