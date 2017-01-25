<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerUpdate extends Command implements CommandInterface
{
    use DockerAware;

    protected function configure()
    {
        $this
            ->setName('composer-update')
            ->setAliases(['cu'])
            ->setDescription('Runs composer update inside the container and pulls back required files to the host');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        system("docker exec $container composer update -o");

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['vendor', 'composer.lock']]);

        $pullCommand->run($pullArguments, $output);
    }
}
