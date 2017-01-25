<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Build extends Command implements CommandInterface
{
    use DockerAware;

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Runs docker build to create an image ready for use')
            ->addOption('prod', 'p', InputOption::VALUE_NONE, 'Build in production mode');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $buildArg = $input->getOption('prod') ? '--build-arg BUILD_ENV=prod' : '';

        system(sprintf('docker build -t mikeymike/m2-demo-php -f app.php.dockerfile %s ./', $buildArg));

        $output->writeln('Build complete!');
    }
}
