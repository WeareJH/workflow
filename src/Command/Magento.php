<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento extends Command implements CommandInterface
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

    protected function configure()
    {
        $this
            ->setName('magento')
            ->setAliases(['mage', 'm'])
            ->setDescription('Works as a proxy to the Magento bin inside the container')
            ->addArgument('cmd', InputArgument::REQUIRED, 'Magento command you want to run')
            ->ignoreValidationErrors();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $slicePoint = 1 + (int) array_search($this->getName(), $_SERVER['argv'], true);
        $args       = array_slice($_SERVER['argv'], $slicePoint);

        if (0 === count($args)) {
            throw new \RuntimeException('No magento command defined!');
        }

        $this->processBuilder->setArguments(array_merge([
            'docker exec',
            '-u www-data',
            $container,
            'bin/magento'
        ], $args));

        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            Process::ERR === $type
                ? $output->writeln('ERR > '. $buffer)
                : $output->writeln('OUT > '. $buffer);
        });
    }
}
