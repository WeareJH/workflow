<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerUpdate extends Command implements CommandInterface
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
            ->setName('composer-update')
            ->setAliases(['cu'])
            ->setDescription('Runs composer update inside the container and pulls back required files to the host');
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

        $command = sprintf(
            'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache %s composer update %s',
            $container,
            implode(' ', $flags)
        );
        $this->runProcessShowingOutput($output, $command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['.docker/composer-cache', 'vendor', 'composer.lock']]);

        $pullCommand->run($pullArguments, $output);
    }
}
