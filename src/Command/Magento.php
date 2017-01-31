<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento extends Command implements CommandInterface
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
            ->setName('magento')
            ->setAliases(['mage', 'm'])
            ->setDescription('Works as a proxy to the Magento bin inside the container')
            ->addArgument('cmd', InputArgument::REQUIRED, 'Magento command you want to run')
            ->ignoreValidationErrors();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $slicePoint = 1 + (int) array_search($this->getName(), $_SERVER['argv'], true);
        $args       = array_slice($_SERVER['argv'], $slicePoint);

        if (0 === count($args)) {
            throw new \RuntimeException('No magento command defined!');
        }

        $command = sprintf('docker exec -u www-data %s bin/magento %s', $container, implode(' ', $args));
        $this->runProcessShowingOutput($output, $command);
    }
}
