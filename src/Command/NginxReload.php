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

    public function __invoke(array $arguments)
    {
        $container = $this->nginxContainerName();
        `docker exec $container nginx -s 'reload'`;
    }

    public function getHelpText(): string
    {
        return 'Reloads NGINX configuration files for when you\'ve made changes to them';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
