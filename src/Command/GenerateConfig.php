<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Config\ConfigGeneratorFactory;
use Jh\Workflow\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GenerateConfig extends Command implements CommandInterface
{
    /**
     * @var ConfigGeneratorFactory
     */
    private $configGeneratorFactory;

    public function __construct(ConfigGeneratorFactory $configGeneratorFactory)
    {
        parent::__construct();
        $this->configGeneratorFactory = $configGeneratorFactory;
    }

    public function configure()
    {
        $this
            ->setName('generate-config')
            ->setAliases(['gc'])
            ->setDescription('Generate the environment config for your instance, e.g. env.ph or local.xml')
            ->addOption('m1', null, InputOption::VALUE_NONE, 'Generate M1 local.xml instead of M2 env.php')
            ->addOption(
                'root-dir',
                null,
                InputOption::VALUE_OPTIONAL,
                'Root dir for write operations, default is CWD',
                getcwd()
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);

        $platform = $input->getOption('m1') ? Platform::M1() : Platform::M2();
        $rootDir  = $input->getOption('root-dir');

        if (!file_exists($rootDir) || !is_dir($rootDir)) {
            throw new \RuntimeException(sprintf('Expected "%s" to be project root directory', $rootDir));
        }

        $this->configGeneratorFactory->create($platform)->generateEnvironmentConfig($rootDir, $output);
    }
}
