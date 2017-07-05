<?php

namespace Jh\WorkflowTest\Test\Constraint;

use Jh\Workflow\Test\Constraint\FileExistsInContainer;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @runTestsInSeparateProcesses Because you cannot mock global functions which already used
 */
class FileExistsInContainerTest extends TestCase
{
    use PHPMock;

    public function testMatchesWhenFileExists()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 0;
            }
        );

        self::assertTrue((new FileExistsInContainer('m2-php'))->matches('some-file.txt'));
    }

    public function testMatchesWhenFileDoesNotExist()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 1;
            }
        );

        self::assertFalse((new FileExistsInContainer('m2-php'))->matches('some-file.txt'));
    }

    public function testToString()
    {
        self::assertEquals('exists in container m2-php', (new FileExistsInContainer('m2-php'))->toString());
    }
}
