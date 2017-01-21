<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoConfigure extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        `docker exec $container magento-configure`;
    }

    public function getHelpText(): string
    {
        return 'Adds Redis configuration for sessions, frontend cache and full page cache to the magento env.php file';
    }
}
