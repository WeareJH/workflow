<?php

namespace Jh\Workflow\NewProject;

use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class DetailsGatherer
{
    private $versionMap = [
        'EE' => 'enterprise',
        'CE' => 'community',
    ];

    public function gatherDetails(OutputStyle $output)
    {
        $output->note('Gathering Details..');

        $repo = $output->ask('GitHub repository? Go create an empty repo first', null, function ($answer) {
            if (!preg_match('/^git@github\.com:WeareJH\/[a-z0-9-]+.git$/', $answer)) {
                throw new \RuntimeException('GitHub url looks incorrect. Make sure it\'s the SSH url');
            }
            return $answer;
        });

        preg_match('/^git@github\.com:WeareJH\/([a-z0-9-]+).git$/', $repo, $matches);

        $projectName = $matches[1];

        if (file_exists($projectName)) {
            throw new \RuntimeException(sprintf('folder %s exists already', $projectName));
        }

        $namespace = $output->ask('Project Namespace? Eg: Neom', null, function ($answer) {
            if (!preg_match('/^[A-Z][a-z]+$/', $answer)) {
                throw new \RuntimeException('Package name must be a valid PHP namespace. Eg: Neom');
            }
            return $answer;
        });

        $version = $this->versionMap[$output->choice('Magento edition?', array_keys($this->versionMap), 'CE')];

        $defaultPub = null;
        $defaultPriv = null;
        if (file_exists(getenv('HOME') . '/.composer/auth.json')) {
            $data = json_decode(file_get_contents(getenv('HOME') . '/.composer/auth.json'), true);

            if (isset($data['http-basic']['repo.magento.com'])) {
                $defaultPub = $data['http-basic']['repo.magento.com']['username'];
                $defaultPriv = $data['http-basic']['repo.magento.com']['password'];
            }
        }

        $pubKey   = $output->ask('Public auth key?', $defaultPub);
        $privKey  = $output->ask('Private auth key?', $defaultPriv);

        $rabbitMq = $version === $this->versionMap['EE']
            ? $output->confirm('Include Rabbit MQ?')
            : false;

        return new Details(
            $repo,
            $projectName,
            $namespace,
            $version,
            $pubKey,
            $privKey,
            $rabbitMq
        );
    }
}
