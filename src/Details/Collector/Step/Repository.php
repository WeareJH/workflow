<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\CollectorException;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class Repository implements StepInterface
{
    const LABEL = 'Git repository';
    const OPTION = 'repo';

    const FILE_CONFIG  = '.git/config';
    const PATTERN = '/^[a-z]+@[a-z-]+\.[a-z\.]{2,}:[A-z]+\/[A-z\d-\.]+$/';

    private function validate($repo) : bool
    {
        return preg_match(self::PATTERN, $repo);
    }

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $repo = $input->getOption(self::OPTION);
        if (0 < strlen($repo)) {
            if (! $this->validate($repo)) {
                throw new CollectorException("{$repo} does not resemble an SSH URL");
            }
            $data->setRepository($repo)->addVisibleData(self::LABEL, $repo);
            return;
        }

        $path = $data->getPath();
        $configPath =  "{$path}/" . self::FILE_CONFIG;
        if (file_exists($configPath)) {
            $ini = parse_ini_file($configPath, true);
            if (isset($ini['remote origin'])) {
                $repo = $ini['remote origin']['url'];
            }
        }

        if (0 < strlen($repo)) {
            $style->note("Detected existing git remote: {$repo}");
            if (! $this->validate($repo)) {
                throw new CollectorException("{$repo} does not resemble an SSH URL");
            }
            $data->setRepository($repo)->addVisibleData(self::LABEL, $repo);
            return;
        }

        $repo = $style->ask('Enter a git repository URL', null, function ($answer) {
            if (! $this->validate($answer)) {
                throw new \RuntimeException("{$answer} does not resemble an SSH URL");
            }
            return $answer;
        });

        $style->caution('If this repository does not already exist, create it now!');
        $data->setRepository($repo)->addVisibleData(self::LABEL, $repo);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_REQUIRED, self::LABEL);
    }
}
