<?php

namespace Jh\Workflow;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Files
{
    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $currentWorkingDirectory;

    public function __construct(ProcessFactory $processFactory, OutputInterface $output)
    {
        $this->processFactory = $processFactory;
        $this->output = $output;
        $this->currentWorkingDirectory = getcwd();
    }

    public function download(string $container, array $files)
    {
        $files = collect($files);

        if ($files->isEmpty()) {
            return;
        }

        $dirsToCreate = $files
            ->map(function ($file) {
                return $this->getRelativePath($file);
            })
            ->map(toMap('dirname'))
            ->reject(function ($dir) {
                return $dir === '.';
            })
            ->map(toMap('escapeshellarg'));

        $downloadFiles = function () use ($container, $files) {
            $files->each(function ($file) use ($container) {
                $this->runCommand(
                    sprintf(
                        'docker cp %s:%s %s',
                        $container,
                        $this->getContainerLocationFromSource($file),
                        dirname($this->getRelativePath($file)) . '/'
                    ),
                    function () use ($file) {
                        $this->output->writeln(
                            sprintf(
                                "<info>Copied '%s' from container into '%s/' on the host</info>",
                                $this->getRelativePath($file),
                                dirname($this->getRelativePath($file))
                            )
                        );
                    }
                );
            });
        };

        if ($dirsToCreate->isNotEmpty()) {
            $makeDirectoriesCommand = sprintf('mkdir -p %s', $dirsToCreate->implode(' '));
            return $this->runCommand($makeDirectoriesCommand, $downloadFiles);
        }

        return $downloadFiles();
    }

    public function upload(string $container, array $files)
    {
        $files = collect($files);

        if ($files->isEmpty()) {
            return;
        }

        $sources = $files->map(function (string $file) {
            return $this->getRelativePath($file);
        });

        $destinations = $sources->map(function (string $source) {
            return $this->getAbsoluteContainerPath($source);
        });

        $makeDirectoriesCommand = sprintf(
            'docker exec %s mkdir -p %s',
            $container,
            $destinations->map(toMap('dirname'))->map(toMap('escapeshellarg'))->implode(' ')
        );

        $this->runCommand($makeDirectoriesCommand, function () use ($container, $sources, $destinations) {
            $this->runCommand(
                $this->getUploadCommand($sources, $container),
                function () use ($container, $sources, $destinations) {
                    $sources->each(function ($file) use ($container) {
                        $this->output->writeln(sprintf('<info> + \'%s\' > %s </info>', $file,  $container));
                    });

                    //chown
                    $this->runCommand(sprintf(
                        'docker exec %s chown -R www-data:www-data %s',
                        $container,
                        $destinations->map(toMap('escapeshellarg'))->implode(' ')
                    ));
                }
            );
        });
    }

    public function delete(string $container, array $files)
    {
        $files = collect($files);

        if ($files->isEmpty()) {
            return;
        }

        $sources = $files->map(function (string $file) {
            return $this->getRelativePath($file);
        });

        $this->runCommand(sprintf(
            'docker exec %s rm -rf  %s',
            $container,
            $sources->map(toMap('escapeshellarg'))->implode(' ')
        ));

        $sources->each(function (string $file) use ($container) {
            $this->output->writeln(sprintf('<fg=red> x %s > %s </fg=red>', $file, $container));
        });
    }

    public function deleteLocally(array $files)
    {
        collect($files)
            ->each(function ($file) {
                $file = $this->getRelativePath($file);
                $this->runCommandSync('rm -rf ' . $this->currentWorkingDirectory . '/' . $file);
            });
    }

    public function existsInContainer(string $container, string $file) : bool
    {
        $file = $this->getContainerLocationFromSource($file);

        try {
            $this->runCommandSync(sprintf('docker exec %s test -e %s', $container, escapeshellarg($file)));
            return true;
        } catch (ProcessFailedException $e) {
            return false;
        }
    }

    private function getRelativePath(string $path) : string
    {
        return trim(str_replace(getcwd(), '', $path), '/');
    }

    public function getAbsoluteContainerPath(string $relativeDestinationPath) : string
    {
        return sprintf('/var/www/%s', $relativeDestinationPath);
    }

    private function getContainerLocationFromSource(string $sourceLocation) : string
    {
        $relativeSource = $this->getRelativePath($sourceLocation);
        return $this->getAbsoluteContainerPath($relativeSource);
    }

    private function runCommand(string $command, callable $onComplete = null)
    {
        $this->processFactory
            ->createAsynchronous(
                $command,
                $this->currentWorkingDirectory,
                function ($output) {
                    $this->output->write($output);
                },
                $onComplete,
                function (\Exception $e) {
                    throw new ProcessFailedException($e->getMessage());
                }
            );
    }

    public function runCommandSync(string $command)
    {
        $process = new \Symfony\Component\Process\Process($command, $this->currentWorkingDirectory);
        $exitCode = $process->run();

        if ($exitCode > 0) {
            throw new ProcessFailedException($process->getErrorOutput());
        }
    }

    private function compressFiles(Collection $relativeFiles) : string
    {
        $filename = sys_get_temp_dir() . '/' . str_replace('.', '', uniqid('', true)) . '.tar';
        $archive = new \PharData($filename);

        $relativeFiles->each(function ($file) use ($archive) {
            $archive->addFile($this->currentWorkingDirectory . '/' . $file, $file);
        });

        $archive->compress(\Phar::GZ);

        unlink($filename);

        return $filename . '.gz';
    }

    private function getUploadCommand(Collection $sources, string $container) : string
    {
        if ($sources->count() === 1) {
            $file = $sources->first();
            return sprintf('docker cp %s %s:/var/www/%s', $file, $container, $file);
        }

        $archive = $this->compressFiles($sources);

        $command  = 'docker cp %s %2$s:/var/www/___files.tar.gz ';
        $command .= '&& docker exec %2$s chown -R www-data:www-data /var/www/___files.tar.gz ';
        $command .= '&& docker exec %2$s tar xzf ___files.tar.gz';

        return sprintf($command, escapeshellarg($archive), $container);
    }
}
