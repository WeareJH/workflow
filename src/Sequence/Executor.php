<?php

namespace Jh\Workflow\Sequence;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

abstract class Executor
{
    /**
     * @var Executor\Step\StepInterface[]
     */
    private $steps = [];

    public function __construct(array $steps = [])
    {
        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    public function addStep(Executor\Step\StepInterface $step) : Executor
    {
        $this->steps[] = $step;
        return $this;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        foreach ($this->steps as $step) {
            $step->execute($data, $style);
        }
    }
}
