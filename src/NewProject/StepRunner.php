<?php

namespace Jh\Workflow\NewProject;

use Jh\Workflow\NewProject\Step\StepInterface;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class StepRunner
{
    /**
     * @var StepInterface[]
     */
    private $steps;

    public function __construct(array $steps)
    {
        $this->steps = array_filter($steps, function ($step) {
            return in_array(StepInterface::class, class_implements($step), true);
        });
    }

    public function run(Details $details, OutputStyle $output)
    {
        foreach ($this->steps as $step) {
            $step->run($details, $output);
        }
    }
}
