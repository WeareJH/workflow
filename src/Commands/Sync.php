<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sync implements \CommandInterface
{
    public function invoke(array $containers, array $arguments)
    {
        // TODO: We don't need to do this anymore, maps are pretty specific
        $containers = [
            'nginx' => ['pub'],
            'php'   => ['./']
        ];

        // TODO: Use containers
        $path          = $argv[1] ?? exit(1);
        $projectPath   = realpath(__DIR__ . '/../');
        $containerPath = ltrim(str_replace($projectPath, '', $path), '/');

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
}
