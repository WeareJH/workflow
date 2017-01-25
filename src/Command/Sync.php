<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Sync extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $this
            ->setName('sync')
            ->setDescription('Syncs changes from the host filesystem to the relevant docker containers')
            ->addArgument('file', InputArgument::REQUIRED, 'The changed file path');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path          = $input->getArgument('file');
        $containerPath = ltrim(str_replace(getcwd(), '', $path), '/');

        $containers = [
            $this->phpContainerName()   => ['./'],
            $this->nginxContainerName() => ['pub']
        ];

        // Filter out uneeded containers
        $containers = array_keys(array_filter($containers, function ($container) use ($containerPath) {
            return in_array('./', $container, true) || array_filter($container, function ($path) use ($containerPath) {
                return strpos($containerPath, $path) === 0;
            });
        }));

        $allowDelete = ($path !== '' && $path !== ' /');

        // Remove the composer command call output
        $output->writeln("\033[2A\033[K\e[1A");

        foreach ($containers as $container) {
            if (file_exists($path)) {
                $output->writeln("\033[32m + $containerPath > $container \033[0m \n");
                `docker cp $path $container:/var/www/$containerPath`;
                continue;
            }

            if (!$allowDelete) {
                $output->writeln("\033[31m Not running rm -rf $containerPath \033[0m");
                $output->writeln("\033[31m Run this manually in the container instead if you really want to...\033[0m");
                $output->writeln("\033[31m docker exec $container rm -rf /var/www/$containerPath \033[0m");
                continue;
            }

            $output->writeln("\033[31m x $containerPath > $container \033[0m \n");
            `docker exec $container rm -rf /var/www/$containerPath`;
        }
    }
}
