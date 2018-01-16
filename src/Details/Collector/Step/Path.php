<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\CollectorException;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

class Path implements StepInterface
{
    const ARG = 'path';
    const LABEL = 'Work path';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $path = $input->getArgument(self::ARG) ?: getcwd();
        if (strpos($path, '/') !== 0) {
            $path = getcwd() . "/{$path}";
        }
        $path = rtrim($path, '/');
        $data->setPath($path)->addVisibleData(self::LABEL, $path);
    }

    public function configure(Command $command)
    {
        $command->addArgument(self::ARG, InputArgument::OPTIONAL, self::LABEL);
    }
}
