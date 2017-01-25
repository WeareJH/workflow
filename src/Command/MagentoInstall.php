<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoInstall extends Command implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $container = $this->phpContainerName();
        system("docker exec $container magento-install");

        $pullCommand = new Pull;
        $pullCommand(['app/etc']);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Runs the magento install script with the relevant environment variables found in the .env file
HTTPS by default.
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
