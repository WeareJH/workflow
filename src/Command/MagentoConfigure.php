<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoConfigure extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    public function configure()
    {
        $this
            ->setName('magento-configure')
            ->setAliases(['mc'])
            ->setDescription('Configures Magento ready for Docker use')
            ->addOption('prod', 'p', InputOption::VALUE_NONE, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $phpContainer   = $this->phpContainerName();
        $mailContainer  = $this->getContainerName('mail');

        $this->runProcessShowingOutput($output, sprintf(
            'docker exec -u www-data %s magento-configure%s',
            $phpContainer,
            $input->getOption('prod') ? ' -p' : ''
        ));

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/env.php']]);

        $pullCommand->run($pullArguments, $output);

        if (!$input->getOption('prod')) {
            $this->configureMail($mailContainer, $output);
        }

        $output->writeln('Configuration complete!');
    }

    private function configureMail($mailContainer, $output)
    {
        $sql =  "DELETE FROM core_config_data WHERE path LIKE 'system/smtp/%'; ";
        $sql .= "INSERT INTO core_config_data (scope, scope_id, path, value) ";
        $sql .= "VALUES ";
        $sql .= "('default', 0, 'system/smtp/host', '$mailContainer'), ";
        $sql .= "('default', 0, 'system/smtp/port', '1025');";

        $sqlCommand   = $this->getApplication()->find('sql');
        $sqlArguments = new ArrayInput(['--sql' => $sql]);

        $sqlCommand->run($sqlArguments, $output);
    }
}
