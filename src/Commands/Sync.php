<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sync extends AbstactDockerCommand implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        if (count($arguments) === 0) {
            echo 'Expected one argument';
        }

        $path          = $arguments[0];
        $projectPath   = realpath(__DIR__ . '/../../../../../');
        $containerPath = ltrim(str_replace($projectPath, '', $path), '/');

        $containers = [
            $this->phpContainerName()   => ['./'],
            $this->nginxContainerName() => ['pub']
        ];

        // Filter out uneeded containers
        $containers = array_keys(array_filter($containers, function ($container) use ($containerPath) {
            return in_array('./', $container, true) || array_filter($container, function ($path) use ($containerPath) {
                return strpos($containerPath, $path) === 0;
            });
        }));

        $allowDelete = ($path !== '' && $path !== ' /');

        foreach ($containers as $container) {
            if (file_exists($path)) {
                echo "\033[32m + $containerPath > $container \033[0m \n";
                `docker cp $path $container:/var/www/$containerPath`;
                continue;
            }

            if (!$allowDelete) {
                echo "\033[31m Not running rm -rf $containerPath \033[0m \n";
                echo "\033[31m Run this manually in the container instead if you really want to... \033[0m \n";
                echo "\033[31m docker exec $container rm -rf /var/www/$containerPath \033[0m \n";
                continue;
            }

            echo "\033[31m x $containerPath > $container \033[0m \n";
            `docker exec $container rm -rf /var/www/$containerPath`;
        }
    }

    public function getHelpText(): string
    {
        return <<<HELP
Pushes changes from the filesystem to the relevant docker containers.

- Nginx will take changes from the pub directory
- PHP will take changes from all directories except .docker.
HELP;
    }
}
