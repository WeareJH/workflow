<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Test extends Command implements CommandInterface
{
    use DockerAwareTrait;

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
            ->setName('test')
            ->setDescription('Run the projects test suite');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $this->processBuilder->setArguments([
            'docker exec',
            '-u www-data',
            $container,
            'vendor/bin/phpcs',
            '-s app/code',
            '--standard=PSR2',
            '--warning-severity=0'
        ]);

        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            $output->writeln($buffer);
        });

        $output->writeln('<info>Tests complete!</info>');
    }
}
