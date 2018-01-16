<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

interface StepInterface
{
    public function execute(DataInterface $data, StyleInterface $style);
}
