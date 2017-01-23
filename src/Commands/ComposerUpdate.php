<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerUpdate implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        system("docker exec $container composer update -o");

        $pullCommand = new Pull();

        $pullCommand(['vendor']);
        $pullCommand(['composer.lock']);
    }

    public function getHelpText(): string
    {
        return 'Will run a composer update inside the container and pull back the vendor directory to the host';
    }
}
