<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private $log;

    public function setUp()
    {
        $this->log = sprintf('%s/%s/workflow.log', sys_get_temp_dir(), $this->getName());
    }
    public function testLogger()
    {
        $logger  = new Logger;
        $logger->setLogFile($this->log);

        $logger->debug('DEBUG MESSAGE');

        $expected  = "/\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \(DEBUG\) DEBUG MESSAGE\\n/";

        self::assertRegExp($expected, file_get_contents($this->log));
    }

    public function tearDown()
    {
        unlink($this->log);
    }
}
