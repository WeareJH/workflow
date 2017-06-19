<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            )
            ->addOption('no-overwrite', 'o', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $overwrite = !$input->getOption('no-overwrite');
        $container = $this->phpContainerName();
        $files     = (array) $input->getArgument('files');

        foreach ($files as $file) {
            $srcPath = ltrim($file, '/');

            if (!$this->fileExistsInContainer($container, $srcPath)) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $srcPath));
                return;
            }

            $destPath = './' . $srcPath;
            if ($overwrite && is_dir($destPath)) {
                //we only remove if the file exists and is a directory
                //as the new directory we push may have a different set of files in it
                //for files we can just overwrite and save some cycles
                $this->runProcessNoOutput('rm -rf ' . $destPath);
            }

            $command = sprintf('docker cp %s:/var/www/%s %s', $container, $srcPath, dirname($destPath) . '/');
            $this->runProcessShowingOutput($output, $command);

            $output->writeln(
                sprintf(
                    "<info>Copied '%s' from container into '%s/' on the host</info>", $srcPath, dirname($destPath))
            );
        }
    }

    private function fileExistsInContainer(string $container, string $destFile) : bool
    {
        try {
            $this->runProcessNoOutput(sprintf('docker exec %s test -e %s', $container, escapeshellarg($destFile)));
            return true;
        } catch (ProcessFailedException $e) {
            return false;
        }
    }
}
