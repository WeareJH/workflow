<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Pull extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
    }

    public function configure()
    {
        $help  = "Pull files from the docker environment to the host, Useful for pulling vendor etc\n\n";
        $help .= 'If the watch is running and you pull a file that is being watched it will ';
        $help .= "automatically be pushed back into the container\n";
        $help .= 'If this is not what you want (large dirs can cause issues here) stop the watch, ';
        $help .= 'pull then start the watch again afterwards';

        $this
            ->setName('pull')
            ->setDescription('Pull files from the docker environment to the host')
            ->setHelp($help)
            ->addArgument(
                'files',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Files to pull, relative to project root'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $files     = (array) $input->getArgument('files');

        foreach ($files as $file) {
            $srcPath = ltrim($file, '/');

            $fileExistsCheck = $this->runProcessNoOutput(sprintf(
                "docker exec %s php -r \"echo file_exists('/var/www/%s') ? 'true' : 'false';\"",
                $container,
                $srcPath
            ));

            if ('false' === $fileExistsCheck->getOutput()) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $srcPath));
                return;
            }

            $destPath = './' . trim(str_replace(basename($srcPath), '', $srcPath), '/');

            $command = sprintf('docker cp %s:/var/www/%s %s', $container, $srcPath, $destPath);
            $this->runProcessShowingOutput($output, $command);

            $output->writeln(
                sprintf("<info>Copied '%s' from container into '%s' on the host</info>", $srcPath, $destPath)
            );
        }
    }
}
