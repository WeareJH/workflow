<?php

namespace Jh\Workflow\Details\Collector\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

class Auth implements StepInterface
{
    const LABEL_GH_OAUTH = 'GitHub OAuth Token';
    const LABEL_MAGE_PUBLIC = 'repo.magento.com public key';
    const LABEL_MAGE_PRIVATE = 'repo.magento.com private key';

    const OPTION = 'auth';

    public function collect(InputInterface $input, DataInterface $data, StyleInterface $style)
    {
        $drill = function (array $arr, array $keys) use (&$drill) {
            $key = array_shift($keys);
            if (array_key_exists($key, $arr)) {
                return count($keys) ? $drill($arr[$key], $keys) : $arr[$key];
            }
            return null;
        };

        $scan = function ($af) use ($drill) {
            if (! file_exists($af)) {
                return ['', '', ''];
            }

            $pub = $priv = $ghoauth = '';
            $data = json_decode(file_get_contents($af), true);

            if ($d = $drill($data, ['github-oauth', 'github.com'])) {
                $ghoauth = $d;
            }

            if ($d = $drill($data, ['http-basic', 'repo.magento.com', 'username'])) {
                $pub = $d;
                $priv = $drill($data, ['http-basic', 'repo.magento.com', 'password']);
            }

            return [$pub, $priv, $ghoauth];
        };

        $getauth = function ($auths) {
            $final = ['', '', ''];
            foreach ($auths as $a) {
                for ($i = 0; $i < 3; $i++) {
                    if (1 > strlen($final[$i])) {
                        $final[$i] = $a[$i];
                    }
                }
            }
            return $final;
        };

        $done = function ($auths) use ($data, $getauth) {
            $final = $getauth($auths);
            if (! in_array('', $final)) {
                $this->setKeys(
                    $data,
                    $final[0],
                    $final[1],
                    $final[2]
                );
                return true;
            }
            return false;
        };

        $path = $data->getPath(); // path should be already set

        $auths = $final = [];

        $file = $input->getOption(self::OPTION);
        if (strlen($file)) {
            if (! file_exists($file)) {
                throw new \RuntimeException("Auth file {$file} does not exist");
            }

            $style->note("Reading auth keys from {$file}");
            $auths[] = $scan($file);
        }

        if ($done($auths)) {
            return;
        }

        $file = "{$path}/auth.json";
        if (file_exists($file)) {
            $style->note("Reading auth keys from {$file}");
            $auths[] = $scan($file);
        }

        if ($done($auths)) {
            return;
        }

        $home = getenv('HOME');
        $file = "{$home}/.composer/auth.json";
        if (file_exists($file) && ! is_dir($file) && $style->confirm("Read auth keys from {$file}?", false)) {
            $style->note("Reading auth keys from {$file}");
            $auths[] = $scan($file);
        }

        if ($done($auths)) {
            return;
        }

        list($pub, $priv, $ghoauth) = $getauth($auths);

        if (1 > strlen($pub)) {
            $pub = $style->ask(self::LABEL_MAGE_PUBLIC, null, function ($answer) {
                if (1 > strlen($answer)) {
                    throw new \RuntimeException('Public key is required');
                }
                return $answer;
            });
        }

        if (1 > strlen($priv)) {
            $priv = $style->askHidden(self::LABEL_MAGE_PRIVATE, function ($answer) {
                if (1 > strlen($answer)) {
                    throw new \RuntimeException('Private key is required');
                }
                return $answer;
            });
        }

        // if (1 > strlen($ghoauth) && strpos($data->getRepository(), 'github.com') !== false) {
        if (1 > strlen($ghoauth)) {
            $ghoauth = $style->askHidden(self::LABEL_GH_OAUTH, function ($answer) {
                if (1 > strlen($answer)) {
                    throw new \RuntimeException('GitHub OAuth key is required');
                }
                return $answer;
            });
        }

        $this->setKeys($data, $pub, $priv, $ghoauth);
    }

    public function configure(Command $command)
    {
        $command->addOption(self::OPTION, 'a', InputOption::VALUE_REQUIRED, 'Use auth.json file');
    }

    private function setKeys(DataInterface $data, $pub, $priv, $ghoauth)
    {
        $data->setMagentoPublicKey($pub)->setMagentoPrivateKey($priv)->setGitHubOAuthToken($ghoauth);

        $safe = function ($key) {
            $l = strlen($key);
            return sprintf("%'#-{$l}s", substr($key, 0, 4));
        };

        $data->addVisibleData(self::LABEL_MAGE_PUBLIC, $pub)
            ->addVisibleData(self::LABEL_MAGE_PRIVATE, $safe($priv))
            ->addVisibleData(self::LABEL_GH_OAUTH, $safe($ghoauth));
    }
}
