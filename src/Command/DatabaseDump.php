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
    use DatabaseConnectorTrait;

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
            ->setName('db:dump')
            ->setDescription('Dump the database to the host')
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Optional database to dump');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainerName('db');
        list($user, $pass, $db) = array_values($this->getDbDetails());
        $db = $input->getOption('database') ?: $db;

        $command = "docker exec -i {$container} mysqldump -u {$user} -p{$pass} {$db} > dump.sql";
        $this->commandLine->runQuietly($command);

        $output->writeln('<info>Database dump saved to ./dump.sql</info>');
    }
}
