<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoFullInstall implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        (new MagentoInstall)($arguments);
        (new MagentoConfigure)($arguments);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs 2 commands as a shortcut on a blank installation

- magento-install
- magento-configure
HELP;
    }
}
