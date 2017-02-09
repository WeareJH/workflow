<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ComposerRequire extends Command implements CommandInterface
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
            ->setName('composer-require')
            ->setAliases(['cr'])
            ->addArgument('package', InputArgument::REQUIRED)
            ->setDescription('Runs composer require inside the container and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $flags     = [];

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
            'docker exec -u www-data %s composer require %s%s',
            $container,
            $input->getArgument('package'),
            count($flags) > 0 ? ' ' . implode(' ', $flags) : ''
        );
        $this->runProcessShowingOutput($output, $command);

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['vendor', 'composer.json', 'composer.lock']]);

        $pullCommand->run($pullArguments, $output);
    }
}
