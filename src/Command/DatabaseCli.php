<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class DatabaseCli extends Command implements CommandInterface
{
    use DatabaseConnectorTrait;

    /**
     * @var CommandLine
     */
    private $cl;

    public function __construct(CommandLine $cl)
    {
        parent::__construct();
        $this->cl = $cl;
    }

    public function configure()
    {
        $this->setName('db:cli')
            ->setAliases(['dc'])
            ->setDescription('Connect to MySQL CLI')
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Optional database to connect to');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        list($user, $pass, $db) = array_values($this->getDbDetails($input));
        $db = $input->getOption('database') ?: $db;

        $command = "docker-compose exec db mysql -u {$user} -p{$pass} {$db}\n";
        $this->cl->runInteractively($command);
    }
}
