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
class ComposerUpdate extends Command implements CommandInterface
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
            ->setName('composer-update')
            ->setAliases(['cu'])
            ->setDescription('Runs composer update inside the container and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();

        $command = sprintf('docker exec %s composer update -o', $container);
        $this->runProcessShowingOutput($output, explode(' ', $command));

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['vendor', 'composer.lock']]);

        $pullCommand->run($pullArguments, $output);
    }
}
