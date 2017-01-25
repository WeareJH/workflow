<?php

namespace Jh\Workflow\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch extends Command implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        $watches  = ['./app', './pub', './composer.json'];
        $excludes = ['.docker', '.*__jp*', '.swp', '.swpx'];

        echo "\e[32mWatching for file changes...\n\n\n \e[39m";
        system(sprintf(
            'fswatch -r %s -e \'%s\' | xargs -n1 -I{} composer run sync {}',
            implode(' ', $watches),
            implode('|', $excludes)
        ));
    }

    public function getHelpText(): string
    {
        return 'Keeps track of filesystem changes, piping the changes to the Sync command';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
