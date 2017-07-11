<?php

namespace Jh\WorkflowTest\Test\Constraint;

use Jh\Workflow\Test\Constraint\FileUserAndGroupInContainer;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @runTestsInSeparateProcesses Because you cannot mock global functions which already used
 */
class FileGroupAndOwnerInContainerTest extends TestCase
{
    use PHPMock;

    public function testMatchesWhenFileUserAndGroupCorrect()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php stat -c "%G:%U" some-file.txt', $command);
                $exitCode = 0;
                $output = 'www-data:www-data';
            }
        );

        self::assertTrue(
            (new FileUserAndGroupInContainer('m2-php', 'www-data', 'www-data'))->matches('some-file.txt')
        );
    }

    public function testMatchesWhenFileUserAndGroupIncorrect()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php stat -c "%G:%U" some-file.txt', $command);
                $exitCode = 0;
                $output = 'root:root';
            }
        );

        self::assertFalse(
            (new FileUserAndGroupInContainer('m2-php', 'www-data', 'www-data'))->matches('some-file.txt')
        );
    }

    public function testMatchesWhenFileDoesNotExist()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php stat -c "%G:%U" some-file.txt', $command);
                $exitCode = 1;
            }
        );

        self::assertFalse(
            (new FileUserAndGroupInContainer('m2-php', 'www-data', 'www-data'))->matches('some-file.txt')
        );
    }

    public function testToString()
    {
        self::assertEquals(
            'has correct group and user in container m2-php',
            (new FileUserAndGroupInContainer('m2-php', 'www-data', 'www-data'))->toString()
        );
    }
}
