<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        if (count($arguments) === 0) {
            echo 'Expected path to file';
            return;
        }

        $container = $this->phpContainerName();
        $srcPath   = trim('/', array_shift($arguments));
        $destPath  = is_dir($srcPath)
            ? trim('/', str_replace(basename($srcPath), '', $srcPath))
            : $srcPath;

        system(sprintf('docker cp %s %s:/var/www/%s', $container, $srcPath, $destPath));
    }

    public function getHelpText(): string
    {
        return <<<HELP
Push files from host to the relevant docker containers. Useful for when the watch isn't running or you want to push loads of files in quickly

Usage: composer x push source_file\033[2m
Where x is the composer script used in your project and source_file is relative to the app path \033[22m
HELP;
    }
}
