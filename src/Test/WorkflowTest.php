<?php

namespace Jh\Workflow\Test;

use Jh\Workflow\Test\Constraint\FileExistsInContainer;
use Jh\Workflow\Test\Constraint\FileUserAndGroupInContainer;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class WorkflowTest extends TestCase
{

    public static function assertFileExistsInContainer(string $filePath, string $container, string $message = '')
    {
        self::assertThat($filePath, new FileExistsInContainer($container), $message);
    }

    public static function assertFileNotExistsInContainer(string $filePath, string $container, string $message = '')
    {
        self::assertThat($filePath, new LogicalNot(new FileExistsInContainer($container)), $message);
    }

    public static function assertFileUserAndGroupInContainer(string $filePath, string $group, string $user, string $container, string $message = '')
    {
        self::assertThat($filePath, new FileUserAndGroupInContainer($container, $group, $user));
    }

    protected function copyFileInToContainer(string $source, string $destination)
    {
        $this->exec(
            sprintf(
                'docker exec m2-php mkdir -p %s',
                dirname($destination)
            )
        );

        $this->exec(
            sprintf(
                'docker cp %s m2-php:%s',
                $source,
                $destination
            )
        );

        self::assertFileExistsInContainer($destination, 'm2-php');
    }

    protected function exec(string $command)
    {
        exec($command, $output, $exitCode);

        if ($exitCode > 0) {
            throw new \RuntimeException('Command failed with exit code: ' . $exitCode);
        }
    }
}
