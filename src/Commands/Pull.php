<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Pull implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        if (count($arguments) === 0) {
            echo 'Expected path to file';
            return;
        }

        $container = $this->phpContainerName();
        $srcPath   = ltrim('/', array_shift($arguments));
        $exists    = (bool) `docker exec $container php -r "echo file_exists('/var/www/$srcPath') ? 'true' : 'false';"`;
        $is_dir    = (bool) `docker exec $container php -r "echo is_dir('/var/www/$srcPath') ? 'true' : 'false';"`;

        if (!$exists) {
            echo sprintf('Looks like "%s" doesn\'t exist', $srcPath);
            return;
        }

        $destPath = $is_dir
            ? trim('/', str_replace(basename($srcPath), '', $srcPath))
            : $srcPath;

        system(sprintf('docker cp %s:/var/www/%s %s', $container, $srcPath, $destPath));
    }

    public function getHelpText(): string
    {
        return <<<HELP
Pull files from the docker environment to the host, Useful for pulling vendor, composer_cache etc

If the watch is running and you pull a file that is being watched it will automatically be pushed back into the container.
If this is not what you want (large dirs can cause issues here) stop the watch, pull then start the watch again afterwards.

Usage: composer x pull source_file \033[2m
Where x is the composer script used in your project and source_file is relative to the app path \033[22m
HELP;
    }
}
