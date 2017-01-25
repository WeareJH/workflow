<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento extends Command implements CommandInterface
{
    use DockerAware;

    protected function configure()
    {
        $this
            ->setName('magento')
            ->setAliases(['mage', 'm'])
            ->setDescription('Works as a proxy to the Magento bin inside the container');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        die(var_dump($input->getArguments()));

        $container = $this->phpContainerName();

        system("docker exec -u www-data $container bin/magento $args");
    }
}
