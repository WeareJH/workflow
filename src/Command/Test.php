<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Test extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Run the projects test suite');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        system("docker exec -u www-data $container vendor/bin/phpcs -s app/code --standard=PSR2 --warning-severity=0");
    }
}
