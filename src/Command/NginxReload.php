<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class NginxReload extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $this
            ->setName('magento-full-install')
            ->setAliases(['mfi'])
            ->setDescription('Sends reload signal to NGINX in the container');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->nginxContainerName();
        `docker exec $container nginx -s 'reload'`;

        $output->writeln('Reload signal sent');
    }
}
