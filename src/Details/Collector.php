<?php

namespace Jh\Workflow\Details;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

abstract class Collector
{
    /**
     * @var Collector\Step\StepInterface[]
     */
    private $steps = [];

    public function __construct(array $steps = [])
    {
        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    public function addStep(Collector\Step\StepInterface $step) : Collector
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * @throws CollectorException
     * @throws Exception
     */
    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        foreach ($this->steps as $step) {
            $step->collect($input, $data, $style);
        }
    }

    public function configure(Command $command) : Collector
    {
        foreach ($this->steps as $step) {
            $step->configure($command);
        }
        return $this;
    }

    public function display(DataInterface $data, StyleInterface $style)
    {
        $info = $data->getVisibleData();

        $ml = 0;
        array_map(function ($i) use (&$ml) {
            $ml = max($ml, strlen($i[0]));
        }, $info);
        $ml += 2;

        $lines = array_map(function ($i) use ($ml) {
            return vsprintf("%-' {$ml}s%s", $i);
        }, $info);

        foreach ($lines as $line) {
            $style->text($line);
        }
    }
}
