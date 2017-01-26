<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sql extends Command implements CommandInterface
{
    use DockerAwareTrait;

    public function configure()
    {
        $this
            ->setName('sql')
            ->setDescription('Run arbitary sql against the database')
            ->addArgument('sql', InputArgument::OPTIONAL, 'SQL to run directly to mysql')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('db');

        if ($input->hasArgument('sql')) {
            $this->runRaw($container, $input->getArgument('sql'));
        }

        if ($input->hasOption('file')) {
            $file = $input->getOption('file');

            if (!file_exists($file)) {
                throw new \RuntimeException('SQL file does not exist!');
            }

            $this->runFile($container, $file);
        }
    }

    private function runRaw(string $container, string $sql)
    {
        $dbDetails = $this->getDbDetails();
        system(sprintf(
            'docker exec -t %s mysql -u%s -p%s %s -e "%s"',
            $container,
            $dbDetails['user'],
            $dbDetails['pass'],
            $dbDetails['db'],
            $sql
        ));
    }

    private function runFile(string $container, string $file)
    {
        echo "Incomplete command, check back later";
        return;
        $dbDetails = $this->getDbDetails();

        // TODO: CP file into the container
        // TODO: Run mysql pushing file in
        // TODO: Remove file from container
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
