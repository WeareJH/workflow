<?php

declare(strict_types=1);

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Diego Cabrejas <diego@wearejh.com>
 */
class MagentoSetupUpgrade extends Command implements CommandInterface
{
    use DockerAwareTrait;

    /**
     * Option to skip deletion of generated/code directory
     */
    const INPUT_KEY_KEEP_GENERATED = 'keep-generated';

    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        parent::__construct();
        $this->commandLine = $commandLine;
    }

    public function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_KEEP_GENERATED,
                null,
                InputOption::VALUE_NONE,
                'Prevents generated files from being deleted. ' . PHP_EOL .
                'We discourage using this option except when deploying to production. ' . PHP_EOL .
                'Consult your system integrator or administrator for more information.'
            )
        ];

        $this
            ->setName('setup:upgrade')
            ->setDescription('Upgrades the Magento application and updates the config.php file')
            ->setDefinition($options);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $option    = $input->getOption(self::INPUT_KEY_KEEP_GENERATED)
            ? '--' . self::INPUT_KEY_KEEP_GENERATED
            : ''
        ;

        $this->commandLine->run(
            sprintf('docker exec -u www-data %s bin/magento setup:upgrade %s --ansi', $container, $option)
        );

        $pullCommand = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/config.php']]);

        $pullCommand->run($pullArguments, $output);
    }
}

