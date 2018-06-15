<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sql extends Command implements CommandInterface
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
            ->setName('sql')
            ->setDescription('Run arbitary sql against the database')
            ->addOption('sql', 's', InputOption::VALUE_OPTIONAL, 'SQL to run directly to mysql')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Path to a file to import')
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Optional database to run SQL');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('db');

        if ($input->getOption('sql')) {
            $this->runRaw($container, $input->getOption('sql'), $input, $output);
        }

        if ($input->getOption('file')) {
            $file = $input->getOption('file');

            if (!file_exists($file) || !is_file($file)) {
                throw new \RuntimeException('SQL file does not exist!');
            }

            $this->runFile($container, $file, $input, $output);
        }
    }

    private function runRaw(string $container, string $sql, InputInterface $input, OutputInterface $output)
    {
        $details = $this->getDbDetails($input);
        $user = $details['user'];
        $pass = $details['pass'];
        $db   = $details['db'];

        $this->commandLine->run(
            sprintf('docker exec -t %s mysql -u%s -p%s %s -e "%s"', $container, $user, $pass, $db, $sql)
        );
    }

    private function runFile(string $container, string $file, InputInterface $input, OutputInterface $output)
    {
        $details = $this->getDbDetails($input);
        $user = $details['user'];
        $pass = $details['pass'];
        $db   = $details['db'];

        if ($this->commandLine->commandExists('pv')) {
            $command = sprintf(
                'pv -f %s | docker exec -i %s mysql -u%s -p%s -D %s',
                escapeshellarg($file),
                escapeshellarg($container),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($db)
            );
        } else {
            $command = sprintf(
                'docker exec -i %s mysql -u%s -p%s %s < %s',
                escapeshellarg($container),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($db),
                escapeshellarg($file)
            );
        }

        $this->commandLine->run($command);
        $output->writeln('<info>DB import complete!</info>');
    }

    private function getDbDetails(InputInterface $input) : array
    {
        $envVars = $this->getDevEnvironmentVars();

        return [
            'user' => 'root',
            'pass' => $envVars['MYSQL_ROOT_PASSWORD'] ?? 'docker',
            'db'   => $input->getOption('database') ?? $envVars['MYSQL_DATABASE'] ?? 'docker'
        ];
    }
}
