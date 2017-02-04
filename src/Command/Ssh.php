<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Ssh extends Command implements CommandInterface
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
            ->setName('ssh')
            ->setDescription('Open up bash into the app container')
            ->addOption('root', 'r', InputOption::VALUE_NONE, 'Open as root user');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $user      = $input->getOption('root') ? 'root' : 'www-data';

        $command = sprintf('docker exec -it -u %s %s bash', $user, $container);

        $process = $this->processFactory->create($command);
        $process->setTty(true);

        $process->run(function ($type, $out) use ($output) {
            $output->write($out);
        });
    }
}
