<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento extends AbstactDockerCommand implements CommandInterface
{

    public function __invoke(array $arguments)
    {
        // TODO: Implement __invoke() method.
    }

    public function getHelpText(): string
    {
        return <<<HELP
Works as a proxy to the Magento bin. Pass the command and arguments as you normally would

Usage: composer x magento cache-flush config
HELP;
    }
}
