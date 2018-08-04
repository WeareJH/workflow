<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Build extends Command implements CommandInterface
{
    use DockerAwareTrait;

    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        parent::__construct();
        $this->commandLine = $commandLine;
    }

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Runs docker build to create an image ready for use')
            ->addOption('prod', 'p', InputOption::VALUE_NONE, 'Ommits development configurations')
            ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip the build cache')
            ->addOption('service', 's', InputOption::VALUE_REQUIRED, 'Service to build');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $service  = $this->getServiceConfig('php');

        if (!isset($service['image'])) {
            throw new \RuntimeException('No image specified for PHP container');
        }

        $dockerFile = $input->getOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $service = $input->getOption('service') ?: 'php';
        $args    = $input->getOption('no-cache') ? '--no-cache' : '';

        $this->commandLine->run(
            rtrim(sprintf('docker-compose -f docker-compose.yml -f %s build %s %s', $dockerFile, $service, $args))
        );

        $output->writeln('<info>Build complete!</info>');
    }
}
