<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\DockerAwareTrait;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class DockerAwareTraitTest extends AbstractTestCommand
{
    private $implementation;

    public function setUp()
    {
        parent::setUp();

        $this->implementation = new class()
        {
            use DockerAwareTrait;

            public function getDevEnvironmentVarsTest()
            {
                return $this->getDevEnvironmentVars();
            }

            public function containerNameTest(string $name)
            {
                $this->getContainerName($name);
            }
        };
    }

    public function testExceptionIsThrownIfServiceDoesntExist()
    {
        $this->useValidEnvironment();
        $this->expectException(\RuntimeException::class);
        $this->implementation->containerNameTest('non-existant-service');
    }

    public function testExceptionIsThrownWhenComposeFileUnParsable()
    {
        $this->useBrokenEnvironemt();
        $this->expectException(\RuntimeException::class);
        $this->implementation->containerNameTest('php');
    }

    public function testExceptionIsThrownIfLocalEnvFileDoesntExist()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);
        $this->implementation->getDevEnvironmentVarsTest();
    }
}
