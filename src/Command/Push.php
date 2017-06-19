<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push extends Command implements CommandInterface
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
        $this
            ->setName('push')
            ->setDescription('Push files from host to the container')
            ->addArgument(
                'files',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Files to push, relative to project root'
            )
            ->addOption('no-overwrite', 'o', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $overwrite = !$input->getOption('no-overwrite');
        $container = $this->phpContainerName();
        $files     = (array) $input->getArgument('files');

        foreach ($files as $file) {
            $srcPath   = trim(str_replace(getcwd(), '', $file), '/');
            $destFile  = sprintf('/var/www/%s', $srcPath);
            $destPath  = str_replace(basename($destFile), '', $destFile);

            if (!file_exists($srcPath)) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $srcPath));
                return;
            }

            if ($overwrite && is_dir($srcPath) && $this->fileExistsInContainer($container, $destFile)) {
                //we only remove on container first if it is a directory
                //as the new directory we push may have a different set of files in it
                //for files we can just overwrite and save some cycles
                $this->runProcessShowingOutput(
                    $output,
                    sprintf('docker exec %s rm -rf %s', $container, escapeshellarg($destFile))
                );
            }

            $mkdirCommand = sprintf(
                'docker exec %s mkdir -p %s',
                $container,
                escapeshellarg(dirname($destFile))
            );
            $copyCommand  = sprintf(
                'docker cp %s %s:%s',
                escapeshellarg($srcPath),
                $container,
                escapeshellarg($destPath)
            );
            $chownCommand = sprintf(
                'docker exec %s chown -R www-data:www-data %s',
                $container,
                escapeshellarg($destFile)
            );

            $this->runProcessShowingOutput($output, $mkdirCommand);
            $this->runProcessShowingOutput($output, $copyCommand);
            $this->runProcessNoOutput($chownCommand);

            $output->writeln(
                sprintf("<info> + %s > %s </info>", $srcPath, $container)
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
