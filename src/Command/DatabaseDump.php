<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
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
        $details = $this->getDbDetails($input);
        $user = $details['user'];
        $pass = $details['pass'];
        $db   = $details['db'];

        $command = sprintf('docker exec -i %s mysqldump -u%s -p%s %s > dump.sql', $container, $user, $pass, $db);
        $this->commandLine->runQuietly($command);
    }

    private function getDbDetails(InputInterface $input) : array
    {
        $envVars   = $this->getDevEnvironmentVars();
        return [
            'user' => 'root',
            'pass' => $envVars['MYSQL_ROOT_PASSWORD'] ?? 'docker',
            'db'   => $input->getOption('database') ?? $envVars['MYSQL_DATABASE'] ?? 'docker'
        ];
    }
}
