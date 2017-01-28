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
class Build extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessBuilder $processBuilder)
    {
        parent::__construct();
        $this->processBuilder = $processBuilder;
    }

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Runs docker build to create an image ready for use')
            ->addOption('prod', 'p', InputOption::VALUE_NONE, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $service  = $this->getServiceConfig('php');
        $buildArg = $input->getOption('prod') ? '--build-arg BUILD_ENV=prod' : '';

        if (!isset($service['image'])) {
            throw new \RuntimeException('No image specified for PHP container');
        }

        $command = sprintf('docker build -t %s -f app.php.dockerfile %s ./', $service['image'], $buildArg);
        $args    = array_values(array_filter(explode(' ', $command)));
        $this->runProcessShowingOutput($output, $args);

        $output->writeln('<info>Build complete!</info>');
    }
}
