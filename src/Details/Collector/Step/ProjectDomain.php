<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class ProjectDomain implements StepInterface
{
    const LABEL = 'Project domain';
    const OPTION = 'domain';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $domain = $input->getOption(self::OPTION);
        if (0 < strlen($domain)) {
            $data->setProjectDomain($domain)->addVisibleData(self::LABEL, $domain);
            return;
        }

        $default = $data->getProjectDomain();

        $domain = $style->ask('Enter a project domain', $default, function ($answer) {
            if (1 > strlen($answer)) {
                throw new \RuntimeException('Project domain is required');
            }
            if (! preg_match('/^[a-z\d-\.]+\.[a-z\d]{2,}$/', $answer)) {
                throw new \RuntimeException('Invalid project domain');
            }
            return $answer;
        });

        $data->setProjectDomain($domain)->addVisibleData(self::LABEL, $domain);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_REQUIRED, self::LABEL);
    }
}
