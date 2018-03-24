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
class Ssh extends Command implements CommandInterface
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
            ->setName('ssh')
            ->setDescription('Open up bash into the app container')
            ->addOption('root', 'r', InputOption::VALUE_NONE, 'Open as root user')
            ->addOption('container', 'c', InputOption::VALUE_REQUIRED, 'Container to SSH into');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $input->getOption('container')
            ? $this->getContainerName($input->getOption('container'))
            : $this->phpContainerName();

        $user = $input->getOption('root') ? 'root' : 'www-data';

        $width = trim(`tput cols`);
        $height = trim(`tput lines`);

        $command = <<<CMD
docker exec \
    -it \
    -u "{$user}" \
    -e COLUMNS="{$width}" \
    -e LINES="{$height}" \
    "{$container}" bash
CMD
        ;

        $this->commandLine->runInteractively($command);
    }
}
