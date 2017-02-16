<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Php extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    protected function configure()
    {
        $this
            ->setName('php')
            ->setDescription('Run a php script on the app container')
            ->addArgument('php-file', InputOption::VALUE_REQUIRED, 'Path to PHP file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $command = sprintf('docker exec -it -u www-data %s php %s', $container, $input->getArgument('php-file'));

        $process = $this->processFactory->create($command);
        $process->setTty(true);

        $process->run(function ($type, $out) use ($output) {
            $output->write($out);
        });
    }
}
