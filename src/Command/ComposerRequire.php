<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ComposerRequire extends Command implements CommandInterface
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
            ->setName('composer-require')
            ->setAliases(['cr'])
            ->addArgument('package', InputArgument::REQUIRED)
            ->addOption('dev', 'd', InputOption::VALUE_NONE, 'Require as dev dependency')
            ->setDescription('Runs composer require inside the container and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $flags     = ['--ansi'];

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

        if ($input->getOption('dev')) {
            $flags[] = '--dev';
        }

        $command = sprintf(
            'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache %s composer require %s %s',
            $container,
            $input->getArgument('package'),
            implode(' ', $flags)
        );
        $this->commandLine->run($command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullFiles     = ['.docker/composer-cache', 'vendor', 'composer.json', 'composer.lock'];
        $pullArguments = new ArrayInput(['files' => $pullFiles]);

        $pullCommand->run($pullArguments, $output);
    }
}
