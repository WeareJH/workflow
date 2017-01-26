<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch extends Command implements CommandInterface
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    public function __construct(ProcessBuilder $processBuilder)
    {
        parent::__construct();
        $this->processBuilder = $processBuilder;
    }

    public function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Keeps track of filesystem changes, piping the changes to the sync command');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $bin = implode(' ', array_slice(
            $_SERVER['argv'],
            0,
            array_search($this->getName(), $_SERVER['argv'], true)
        ));

        $watches  = ['./app', './pub', './composer.json'];
        $excludes = ['.docker', '.*__jp*', '.swp', '.swpx'];

        $output->writeln("<info>Watching for file changes...</info>");

        $this->processBuilder->setArguments([
            'fswatch',
            sprintf('-r %s', implode(' ', $watches)),
            sprintf("-e '%s'", implode('|', $excludes)),
            '|',
            'xargs -n1 -I{}',
            sprintf('%s sync {}', $bin)
        ]);

        $process = $this->processBuilder->setTimeout(null)->getProcess();
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->writeln('ERR > ' . $buffer);
            }
        });
    }
}
