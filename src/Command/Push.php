<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
{
    $this
        ->setName('pull')
        ->setDescription('Push files from host to the container')
        ->addArgument(
            'files',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Files to push, relative to project root'
        );
}

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $files     = is_array($input->getArgument('file'))
            ? $input->getArgument('file')
            : [$input->getArgument('file')];

        foreach ($files as $file) {
            $srcPath  = trim($file, '/');
            $destPath = trim(str_replace(basename($srcPath), '', $srcPath), '/');

            system(sprintf('docker cp %s %s:/var/www/%s', $srcPath, $container, $destPath));
            $output->writeln(sprintf("\e[32mCopied '%s' into '%s' on the container \e[39m", $srcPath, $destPath));
        }
    }
}
