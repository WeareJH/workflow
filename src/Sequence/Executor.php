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
        foreach ($steps as $id => $step) {
            $this->addStep($id, $step);
        }
    }

    public function addStep($id, Executor\Step\StepInterface $step) : Executor
    {
        $this->steps[$id] = $step;
        return $this;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        foreach ($this->getStepIds() as $id) {
            $this->executeStep($id, $data, $style);
        }
    }

    public function executeStep($id, DataInterface $data, StyleInterface $style)
    {
        if (! array_key_exists($id, $this->steps)) {
            throw new \RuntimeException("Step {$id} does not exist");
        }
        $this->steps[$id]->execute($data, $style);
    }

    public function getStepIds()
    {
        return array_keys($this->steps);
    }
}
