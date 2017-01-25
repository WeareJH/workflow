<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
interface CommandInterface
{
    public function execute(InputInterface $input, OutputInterface $output);
}
