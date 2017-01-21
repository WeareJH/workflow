<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoFullInstall extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        // TODO: Implement __invoke() method.
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
