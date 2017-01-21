<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstall extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        `docker exec $container magento-install`;
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs the magento install script with the relevant environment variables found in the .env file
HTTPS by default.
HELP;
    }
}
