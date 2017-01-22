<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Build implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        if (count($arguments) > 0 && 'prod' === $arguments[0]) {
            system('docker build -t mikeymike/m2-demo-php -f app.php.dockerfile --build-arg BUILD_ENV=prod ./');
            return;
        }

        system('docker build -t mikeymike/m2-demo-php -f app.php.dockerfile ./');
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs docker build to create an image ready for use

Use argument prod to build in production mode  \033[2m
(Not yet fully geared to full production deployments) \033[22m

Usage: composer x build [prod]
HELP;
    }
}
