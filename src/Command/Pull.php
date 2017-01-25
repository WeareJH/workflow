<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Pull extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $description  = "Pull files from the docker environment to the host, Useful for pulling vendor etc\n\n";
        $description .= 'If the watch is running and you pull a file that is being watched it will ';
        $description .= "automatically be pushed back into the container\n";
        $description .= "If this is not what you want (large dirs can cause issues here) stop the watch, ";
        $description .= "pull then start the watch again afterwards";

        $this
            ->setName('pull')
            ->setDescription($description)
            ->addArgument(
                'files',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Files to pull, relative to project root'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $files     = is_array($input->getArgument('file'))
            ? $input->getArgument('file')
            : [$input->getArgument('file')];

        foreach ($files as $file) {
            $srcPath = ltrim($file, '/');
            $exists = (bool)`docker exec $container php -r "echo file_exists('/var/www/$srcPath') ? 'true' : 'false';"`;

            if (!$exists) {
                echo sprintf('Looks like "%s" doesn\'t exist', $srcPath);
                return;
            }

            $destPath = './' . trim(str_replace(basename($srcPath), '', $srcPath), '/');

            system(sprintf('docker cp %s:/var/www/%s %s', $container, $srcPath, $destPath));
            $output->writeln(
                sprintf("\e[32mCopied '%s' from container into '%s' on the host \e[39m", $srcPath, $destPath)
            );
        }
    }
}
