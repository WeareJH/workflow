<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class ProjectNamespace implements StepInterface
{
    const LABEL = 'Project namespace';
    const OPTION = 'namespace';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $ns = $input->getOption(self::OPTION);
        if (0 < strlen($ns)) {
            $data->setProjectNamespace($ns)->addVisibleData(self::LABEL, $ns);
            return;
        }

        $ns = $style->ask('Enter a project namespace e.g. Jh', null, function ($answer) {
            if (! preg_match('/^[A-z][a-z]+$/', $answer)) {
                throw new \RuntimeException('Invalid project namespace');
            }
            return $answer;
        });

        $data->setProjectNamespace($ns)->addVisibleData(self::LABEL, $ns);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_REQUIRED, self::LABEL);
    }
}
