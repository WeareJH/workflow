<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch extends Command implements CommandInterface
{
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    public function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Keeps track of filesystem changes, piping the changes to the sync command');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $watches  = ['./app', './pub', './composer.json'];
        $excludes = ['.docker', '.*__jp*', '.swp', '.swpx'];

        $output->writeln("<info>Watching for file changes...</info>");
        $output->writeln('');

        $command = sprintf(
            'fswatch -r %s -e "%s" | xargs -n1 -I {} workflow sync {}',
            implode(' ', $watches),
            implode('|', $excludes)
        );

        $this->runProcessShowingOutput($output, $command);
    }
}
