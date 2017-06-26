<?php

namespace Jh\Workflow;

use Illuminate\Support\Collection;
use React\ChildProcess\Process;
use Rx\React\ProcessSubject;
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

    public function __construct(ProcessFactory $processFactory, OutputInterface $output)
    {
        $this->processFactory = $processFactory;
        $this->output = $output;
    }


    public function upload(string $container, array $files)
    {
        $files = collect($files);

        if ($files->isEmpty()) {
            return;
        }

        $currentDir = getcwd();

        $sources = $files->map(function (string $file) use ($currentDir) {
            return trim(str_replace($currentDir, '', $file), '/');
        });

        $destinations = $sources->map(function (string $source) {
            return sprintf('/var/www/%s', $source);
        });

        $makeDirectoriesCommand = sprintf(
            'docker exec %s mkdir -p %s',
            $container,
            $files->map(toMap('dirname'))->map(toMap('escapeshellarg'))->implode(' ')
        );

        $this->runCommand($makeDirectoriesCommand, function () use ($currentDir, $container, $sources, $destinations) {
            $this->runCommand(
                $this->getUploadCommand($currentDir, $sources, $container),
                function () use ($container, $sources, $destinations) {
                    $sources->each(function ($file) use ($container) {
                        $this->output->writeln(sprintf('<info> + %s > %s </info>', $file,  $container));
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

        $currentDir = getcwd();

        $sources = $files->map(function (string $file) use ($currentDir) {
            return trim(str_replace($currentDir, '', $file), '/');
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

    private function runCommand(string $command, callable $onComplete = null)
    {
        $process = new ProcessSubject($command);
        $process->subscribe(
            function ($output) {
                $this->output->write($output);
            },
            function (\Exception $e) {
                throw new ProcessFailedException($e->getMessage());
            },
            $onComplete
        );
    }

    private function compressFiles(string $baseDirectory, Collection $relativeFiles) : string
    {
        $filename = sys_get_temp_dir() . '/' . str_replace('.', '', uniqid('', true)) . '.tar';
        $archive = new \PharData($filename);

        $relativeFiles->each(function ($file) use ($baseDirectory, $archive) {
            $archive->addFile($baseDirectory . '/' . $file, $file);
        });

        $archive->compress(\Phar::GZ);

        unlink($filename);

        return $filename . '.gz';
    }

    private function getUploadCommand(string $currentDir, Collection $sources, string $container) : string
    {
        if ($sources->count() === 1) {
            $file = $sources->first();
            return sprintf('docker cp %s %s:/var/www/%s', $file, $container, $file);
        }

        $archive = $this->compressFiles($currentDir, $sources);

        $command  = 'docker cp %s %2$s:/var/www/___files.tar.gz ';
        $command .= '&& docker exec %2$s chown -R www-data:www-data /var/www/___files.tar.gz ';
        $command .= '&& docker exec %2$s tar xzf ___files.tar.gz';

        return sprintf($command, escapeshellarg($archive), $container);
    }
}
