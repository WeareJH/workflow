<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Build extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        if (count($arguments) > 0 && '-p' === $arguments[0]) {
            `docker build -t mikeymike/m2-demo-php -f app.php.dockerfile --build-arg BUILD_ENV=prod ./`;
            return;
        }

        `docker build -t mikeymike/m2-demo-php -f app.php.dockerfile ./`;
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs docker build to create an image ready for use

Use argument -p to build in production mode  \033[2m
(Not yet fully geared to full production deployments) \033[22m
HELP;
    }
}
