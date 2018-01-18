<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\CollectorException;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class Path implements StepInterface
{
    const LABEL = 'Work path';
    const OPTION = 'path';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $path = $input->getOption(self::OPTION);
        if (strlen($path)) {
            if (strpos($path, '/') !== 0) {
                $path = getcwd() . "/{$path}";
            }
            $data->setPath($path)->addVisibleData(self::LABEL, $path);
            return;
        }

        // use repo name by default
        $repo = $data->getRepository();
        preg_match("/\/([A-z\d-\.]+)$/", $repo, $match);
        if (2 == count($match)) {
            $default = str_replace('.git', '', $match[1]);
        }
        else {
            $default = null;
        }

        $path = $style->ask('Specify work path', $default, function ($answer) {
            if (1 > strlen($answer)) {
                throw new \RuntimeException('Invalid work path');
            }
            return $answer;
        });

        if (strpos($path, '/') !== 0) {
            $path = getcwd() . "/{$path}";
        }

        $path = rtrim($path, '/');
        $data->setPath($path)->addVisibleData(self::LABEL, $path);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_REQUIRED, self::LABEL);
    }
}
