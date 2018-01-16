<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class MagentoEdition implements StepInterface
{
    const LABEL = 'Magento edition';

    const REQ_CLOUD = 'magento/magento-cloud-metapackage';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $editions = [
            'CE' => 'Community Edition',
            'EE' => 'Enterprise Edition'
        ];

        if ($ed = $this->getEditionFromComposerJson($data)) {
            $ied = $ed == 'EE' ? DataInterface::MAGENTO_EE : DataInterface::MAGENTO_CE;
            $style->note(sprintf('Using %s as specified in composer.json', $editions[$ed]));
            $data->setMagentoEdition($ied)->addVisibleData(self::LABEL, $editions[$ed]);
            return;
        }

        if ($input->getOption('community')) {
            $ed = 'CE';
        } elseif ($input->hasParameterOption('--enterprise', true)) {
            $ed = 'EE';
        } else {
            $ed = $style->choice('Choose a Magento edition', $editions);
        }

        $ied = $ed == 'EE' ? DataInterface::MAGENTO_EE : DataInterface::MAGENTO_CE;
        $data->setMagentoEdition($ied)->addVisibleData(self::LABEL, $editions[$ed]);
    }

    public function configure(Command $command)
    {
        $command->addOption('community', null, InputOption::VALUE_NONE, 'Use Magento Community Edition');
        $command->addOption('enterprise', null, InputOption::VALUE_NONE, 'Use Magento Enterprise Edition');
    }

    private function getEditionFromComposerJson(DataInterface $data)
    {
        $path = $data->getPath();
        $file = "{$path}/composer.json";
        if (! file_exists($file)) {
            return '';
        }
        $json = json_decode(file_get_contents($file), true);
        if (array_key_exists('require', $json) && array_key_exists(self::REQ_CLOUD, $json['require'])) {
            return 'EE';
        }
        return '';
    }
}
