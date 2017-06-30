<?php

namespace Jh\Workflow;

use React\EventLoop\LoopInterface;
use Rx\Observable;
use Rx\React\FsWatch;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class WatchFactory
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function create(array $watches, array $excludes = []) : Observable
    {
        return new FsWatch(
            implode(' ', $watches),
            sprintf('-e %s -l 0.5', implode(' -e ', $excludes)),
            $this->loop
        );
    }
}
