<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstall implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        system("docker exec $container magento-install");

        $pullCommand = new Pull;
        $pullCommand(['app/etc']);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs the magento install script with the relevant environment variables found in the .env file
HTTPS by default.
HELP;
    }
}
