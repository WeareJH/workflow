<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstall extends Command implements CommandInterface
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
            ->setName('magento-install')
            ->setAliases(['mi'])
            ->setDescription('Runs the magento install script');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $this->processBuilder->setArguments([
            'docker exec',
            $container,
            'magento-install'
        ]);

        $process = $this->processBuilder->setTimeout(null)->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            Process::ERR === $type
                ? $output->writeln('ERR > '. $buffer)
                : $output->writeln('OUT > '. $buffer);
        });

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc']]);

        $pullCommand->run($pullArguments, $output);

        $output->writeln('Install complete!');
    }
}
