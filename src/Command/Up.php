<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up extends Command implements CommandInterface
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
            ->setName('up')
            ->setDescription('Uses docker-compose to start the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $envDockerFile = $input->hasOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $this->processBuilder->setArguments([
            'docker-compose',
            '-f docker-compose.yml',
            '-f ' . $envDockerFile,
            'up'
        ]);

        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            Process::ERR === $type
                ? $output->writeln('ERR > '. $buffer)
                : $output->writeln('OUT > '. $buffer);
        });
    }
}
