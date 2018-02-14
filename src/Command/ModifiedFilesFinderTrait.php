<?php

declare(strict_types=1);

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Jh\Workflow\ProcessFailedException;

/**
 * @author Diego Cabrejas <diego@wearejh.com>
 */
trait ModifiedFilesFinderTrait
{
    public function getContainerCurrentTime(CommandLine $commandLine, string $containerName) : \DateTimeImmutable
    {
        $startTime = $commandLine->runQuietly(
            sprintf(
                'docker exec -u www-data %s date +"%%Y-%%m-%%d %%H:%%M"',
                $containerName
            )
        );

        return \DateTimeImmutable::createFromFormat("Y-m-d H:i", trim($startTime));
    }

    public function getContainerModifiedFiles(
        CommandLine $commandLine,
        string $containerName,
        \DateTimeImmutable $fromDate,
        array $fromPaths
    ): array
    {
        $result = [];
        foreach ($fromPaths as $path) {

            if (!$this->existsInContainer($containerName, $path)) {
                continue;
            }

            $output  = $commandLine->runQuietly(
                sprintf(
                    "docker exec -u www-data %s find %s/. -maxdepth 1 -newermt '%s' -exec basename {}, \\;",
                    $containerName,
                    $path,
                    $fromDate->format('Y-m-d H:i')
                )
            );

            $modifiedFiles = collect(explode(',', $output))->map(function ($item) {
                return trim($item);
            })->reject(function ($item) {
                return empty($item) || $item === '.';
            })->map(function ($item) use ($path){
                return $path . '/' .$item;
            })->toArray();

            $result = array_merge($result, $modifiedFiles);
        }

        return $result;
    }

    public function existsInContainer(string $container, string $file) : bool
    {
        try {
            $this->commandLine->run(sprintf('docker exec -u www-data %s test -e %s', $container, escapeshellarg($file)));
            return true;
        } catch (ProcessFailedException $e) {
            return false;
        }
    }
}
