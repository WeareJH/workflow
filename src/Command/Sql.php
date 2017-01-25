<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sql extends Command implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        if (count($arguments) === 0) {
            echo 'Expected sql, either directly or path to file with the -f flag';
            return;
        }

        if ('-f' === $arguments[0] && !isset($arguments[1])) {
            echo 'Expected second argument with path to file';
            return;
        }

        $sql = '-f' === $arguments[0]
            ? file_get_contents($arguments[1])
            : $arguments[0];

        $container = $this->getContainerName('db');
        $envVars   = $this->getDevEnvironmentVars();

        $dbUser = $envVars['MYSQL_USER'] ?? 'docker';
        $dbPass = $envVars['MYSQL_PASSWORD'] ?? 'docker';
        $db     = $envVars['MYSQL_DATABASE'] ?? 'docker';

        system(sprintf('docker exec -t %s mysql -u%s -p%s %s -e "%s"', $container, $dbUser, $dbPass, $db, $sql));
    }

    public function getHelpText(): string
    {
        return <<<HELP
Run arbitary sql against the database. Accepts sql string or file with -f flag

The -- is required for this command when passing a file to ensure Composer passes the argument

Usage: 
    composer run sql "SELECT * FROM core_config_data"
    composer run sql -- -f path_to_sql_file.sql
HELP;

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
