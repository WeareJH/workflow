<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

interface StepInterface
{
    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style);
    public function configure(Command $command);
}
