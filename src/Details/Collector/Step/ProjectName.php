<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class ProjectName implements StepInterface
{
    const LABEL = 'Project name';
    const OPTION = 'name';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $name = $input->getOption(self::OPTION);
        if (0 < strlen($name)) {
            $data->setProjectName($name)->addVisibleData(self::LABEL, $name);
            return;
        }

        $name = $style->ask('Enter a project name e.g. JH Website', null, function ($answer) {
            if (1 > strlen($answer)) {
                throw new \RuntimeException('Project name is required');
            }
            return $answer;
        });

        $data->setProjectName($name)->addVisibleData(self::LABEL, $name);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_REQUIRED, self::LABEL);
    }
}
