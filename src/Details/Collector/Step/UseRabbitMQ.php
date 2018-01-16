<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class UseRabbitMQ implements StepInterface
{
    const LABEL = 'RabbitMQ';
    const OPTION = 'rabbitmq';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $ee = $data->getMagentoEdition() == DataInterface::MAGENTO_EE;
        if (! $ee) {
            return;
        }

        if ($input->hasParameterOption('--rabbitmq=0', true)) {
            $r = false;
        } elseif ($input->hasParameterOption('--rabbitmq', true) || $input->hasParameterOption('--rabbitmq=1', true)) {
            $r = true;
        }

        if (! isset($r)) {
            $r = $style->confirm('Use Rabbit MQ?');
        }

        $data->setUseRabbitMQ($r)->addVisibleData(self::LABEL, $r ? 'Yes' : 'No');
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, null, InputOption::VALUE_OPTIONAL, self::LABEL);
    }
}
