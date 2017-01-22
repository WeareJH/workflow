<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        $args      = implode(' ', $arguments);

        system("docker exec $container bin/magento $args");
    }

    public function getHelpText(): string
    {
        return <<<HELP
Works as a proxy to the Magento bin. 

Usage: composer x magento cache-flush config

Note... trying to pass arguments such as -f or --theme="Magento/Luma" will break Composer, instead use -- e.g.

Usage: composer x magento -- setup:static-content:deploy --theme="Magento/Luma"  
HELP;
    }
}
