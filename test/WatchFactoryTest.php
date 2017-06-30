<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\WatchFactory;
use PHPUnit\Framework\TestCase;
use React\EventLoop\StreamSelectLoop;
use Rx\React\FsWatch;

class WatchFactoryTest extends TestCase
{
    public function testWatchFactory()
    {
        $watcher = (new WatchFactory(new StreamSelectLoop))->create(['app']);

        self::assertInstanceOf(FsWatch::class, $watcher);
    }

    public function testWatchFactoryWithExcludes()
    {
        $watcher = (new WatchFactory(new StreamSelectLoop))->create(['app'], ['exclude-me']);

        self::assertInstanceOf(FsWatch::class, $watcher);
    }
}
