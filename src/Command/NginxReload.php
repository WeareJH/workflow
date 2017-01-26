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
class NginxReload extends Command implements CommandInterface
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
            ->setName('nginx-reload')
            ->setAliases(['nginx'])
            ->setDescription('Sends reload signal to NGINX in the container');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('nginx');

        $this->processBuilder->setArguments([
            'docker exec',
            $container,
            'nginx',
            "-s 'reload'"
        ]);

        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            Process::ERR === $type
                ? $output->writeln('ERR > '. $buffer)
                : $output->writeln('OUT > '. $buffer);
        });

        $output->writeln('Reload signal sent');
    }
}
