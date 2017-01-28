<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sql extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessBuilder $processBuilder)
    {
        parent::__construct();
        $this->processBuilder = $processBuilder;
    }

    public function configure()
    {
        $this
            ->setName('sql')
            ->setDescription('Run arbitary sql against the database')
            ->addArgument('sql', InputArgument::OPTIONAL, 'SQL to run directly to mysql')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Path to a file to import');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('db');

        if ($input->hasArgument('sql')) {
            $this->runRaw($container, $input->getArgument('sql'), $output);
        }

        if ($input->hasOption('file')) {
            $file = $input->getOption('file');

            if (!file_exists($file) || !is_file($file)) {
                throw new \RuntimeException('SQL file does not exist!');
            }

            $this->runFile($container, $file, $output);
        }

        $output->writeln('<info>DB file import process complete</info>');
    }

    private function runRaw(string $container, string $sql, OutputInterface $output)
    {
        extract($this->getDbDetails(), EXTR_OVERWRITE);

        $command = sprintf('docker exec -t %s mysql -u%s -p%s %s -e', $container, $user, $pass, $db);
        $this->runProcessShowingErrors($output, array_merge(explode(' ', $command), [sprintf('"%s"', $sql)]));
    }

    private function runFile(string $container, string $file, OutputInterface $output)
    {
        extract($this->getDbDetails(), EXTR_OVERWRITE);

        $command = sprintf('docker cp %s %s:/root/%s', $file, $container, $file);
        $this->runProcessShowingErrors($output, explode(' ', $command));

        $command = sprintf('docker exec %s mysql -u%s -p%s %s < /root/%s', $container, $user, $pass, $db, $file);
        $this->runProcessShowingErrors($output, explode(' ', $command));

        $command = sprintf('docker exec %s rm /root/%s', $container, $file);
        $this->runProcessShowingErrors($output, explode(' ', $command));
    }

    private function getDbDetails() : array
    {
        $envVars   = $this->getDevEnvironmentVars();
        $dbDetails = [];

        $dbDetails['user'] = $envVars['MYSQL_USER'] ?? 'docker';
        $dbDetails['pass'] = $envVars['MYSQL_PASSWORD'] ?? 'docker';
        $dbDetails['db']   = $envVars['MYSQL_DATABASE'] ?? 'docker';

        return $dbDetails;
    }
}
