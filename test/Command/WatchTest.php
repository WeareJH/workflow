<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\Watch;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

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
        $this->command = new Watch($this->processBuilder->reveal());
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

    public function testWatch()
    {
        // We have to use $_SERVER here
        $_SERVER['argv'] = ['workflow', 'watch'];

        $expectedArgs = [
            'fswatch',
            '-r',
            './app',
            './pub',
            './composer.json',
            '-e',
            '".docker|.*__jp*|.swp|.swpx"',
            '|',
            'xargs',
            '-n1',
            '-I{}',
            'workflow',
            'sync',
            '{}'
        ];

        $this->processTest($expectedArgs);
        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }
}
