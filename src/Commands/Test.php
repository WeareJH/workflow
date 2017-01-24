<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Test implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        system("docker exec -u www-data $container vendor/bin/phpcs -s app/code --standard=PSR2 --warning-severity=0");
    }

    public function getHelpText(): string
    {
        return 'Run the projects test suite';
    }
}
