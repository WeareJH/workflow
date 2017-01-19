<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
interface CommandInterface
{
    public function __invoke(array $arguments);
}
