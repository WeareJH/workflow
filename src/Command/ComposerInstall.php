<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ComposerInstall extends Command implements CommandInterface
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
            ->setName('composer-install')
            ->setAliases(['ci'])
            ->setDescription('Runs composer install inside the container and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $flags     = ['-o', '--ansi'];

        switch ($output->getVerbosity()) {
            case OutputInterface::VERBOSITY_VERBOSE:
                $flags[] = '-v';
                break;
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $flags[] = '-vv';
                break;
            case OutputInterface::VERBOSITY_DEBUG:
                $flags[] = '-vvv';
                break;
        }

        $this->commandLine->run(
            sprintf(
                'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache %s composer install %s',
                $container,
                implode(' ', $flags)
            )
        );

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['vendor', '.docker/composer-cache']]);

        $pullCommand->run($pullArguments, $output);
    }
}
