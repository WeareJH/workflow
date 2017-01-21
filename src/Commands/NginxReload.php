<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class NginxReload extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        $container = $this->nginxContainerName();
        `docker exec $container nginx -s 'reload'`;
    }

    public function getHelpText(): string
    {
        return 'Reloads NGINX configuration files for when you\'ve made changes to them';
    }
}
