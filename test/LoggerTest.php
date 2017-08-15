<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class LoggerTest extends TestCase
{
    private $log;

    public function setUp()
    {
        $this->log = sprintf('%s/%s/workflow.log', sys_get_temp_dir(), $this->getName());
    }
    public function testLogger()
    {
        $logger  = new Logger(new BufferedOutput);
        $logger->setLogFile($this->log);

        $logger->debug('DEBUG MESSAGE');

        $expected  = "/\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \(DEBUG\) DEBUG MESSAGE\\n/";

        self::assertRegExp($expected, file_get_contents($this->log));
    }

    public function testLogCommandLogsAndPrints()
    {
        $logger  = new Logger($output = new BufferedOutput);
        $logger->setLogFile($this->log);

        $logger->logCommand('echo "yes"', 'async');

        $expected  = "/\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \(DEBUG\) Executing command \[async\]: \"echo \"yes\"\"\n/";

        self::assertRegExp($expected, file_get_contents($this->log));
        self::assertEquals("Executing [async] echo \"yes\"\n", $output->fetch());
    }

    public function tearDown()
    {
        unlink($this->log);
    }
}
