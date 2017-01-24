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
        $srcPath   = trim(array_shift($arguments), '/');
        $destPath  = trim(str_replace(basename($srcPath), '', $srcPath), '/');

        system(sprintf('docker cp %s %s:/var/www/%s', $srcPath, $container, $destPath));
        echo sprintf("\e[32mCopied '%s' into '%s' on the container \e[39m", $srcPath, $destPath);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Push files from host to the relevant docker containers. Useful for when the watch isn't running or you want to push loads of files in quickly

Usage: composer run push source_file
HELP;
    }
}
