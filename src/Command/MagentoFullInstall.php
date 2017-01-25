<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoFullInstall extends Command implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        (new MagentoInstall)($arguments);
        (new MagentoConfigure)($arguments);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs 2 commands as a shortcut on a blank installation

- magento-install
- magento-configure
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
