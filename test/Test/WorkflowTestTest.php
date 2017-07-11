<?php

namespace Jh\WorkflowTest\Test;

use Jh\Workflow\Test\WorkflowTest;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @runTestsInSeparateProcesses Because you cannot mock global functions which already used
 */
class WorkflowTestTest extends TestCase
{
    use PHPMock;

    public function testAssertFileExistsInContainer()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 0;
            }
        );

        WorkflowTest::assertFileExistsInContainer('some-file.txt', 'm2-php');
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testAssertFileExistsInContainerThrowsExceptionWhenItDoesNot()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 1;
            }
        );

        WorkflowTest::assertFileExistsInContainer('some-file.txt', 'm2-php');
    }

    public function testAssertFileNotExistsInContainer()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 1;
            }
        );

        WorkflowTest::assertFileNotExistsInContainer('some-file.txt', 'm2-php');
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testAssertFileNotExistsInContainerThrowsExceptionWhenItDoes()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 0;
            }
        );

        WorkflowTest::assertFileNotExistsInContainer('some-file.txt', 'm2-php');
    }

    public function testAssertFileUserAndGroupInContainer()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php stat -c "%G:%U" some-file.txt', $command);
                $exitCode = 0;
                $output = 'www-data:www-data';
            }
        );

        WorkflowTest::assertFileUserAndGroupInContainer('some-file.txt', 'www-data', 'www-data', 'm2-php');
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testAssertFileUserAndGroupInContainerThrowsExceptionWhenItDoesNotHaveCorrectUserAndGroup()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 0;
                $output = 'root:root';
            }
        );

        WorkflowTest::assertFileUserAndGroupInContainer('some-file.txt', 'www-data', 'www-data', 'm2-php');
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testAssertFileUserAndGroupInContainerThrowsExceptionWhenFileDoesNotExist()
    {
        $exec = $this->getFunctionMock('Jh\Workflow\Test\Constraint', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$exitCode) {
                self::assertEquals('docker exec m2-php test -e some-file.txt', $command);
                $exitCode = 1;
            }
        );

        WorkflowTest::assertFileUserAndGroupInContainer('some-file.txt', 'www-data', 'www-data', 'm2-php');
    }
}
