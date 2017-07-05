<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Files;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DatabaseDump extends Command implements CommandInterface
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
            ->setName('db-dump')
            ->setDescription('Dump the database to the host')
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Optional database to dump');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('db');

        $this->dump($container, $input);

        $output->writeln('<info>Database dump saved to ./dump.sql</info>');
    }

    private function dump(string $container, InputInterface $input)
    {
        extract($this->getDbDetails($input), EXTR_OVERWRITE);

        $command = sprintf('docker exec -i %s mysqldump -u%s -p%s %s > dump.sql', $container, $user, $pass, $db);
        $this->runProcessNoOutput($command);
    }

    private function getDbDetails(InputInterface $input) : array
    {
        $envVars   = $this->getDevEnvironmentVars();
        $dbDetails = [];

        $dbDetails['user'] = $envVars['MYSQL_USER'] ?? 'docker';
        $dbDetails['pass'] = $envVars['MYSQL_PASSWORD'] ?? 'docker';
        $dbDetails['db']   = $input->getOption('database') ?? $envVars['MYSQL_DATABASE'] ?? 'docker';

        return $dbDetails;
    }
}
