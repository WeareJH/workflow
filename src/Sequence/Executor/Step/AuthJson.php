<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class AuthJson implements StepInterface
{
    /**
     * @var Template
     */
    private $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $path = $data->getPath();
        $file = "{$path}/auth.json";

        $exists = file_exists($file);

        if ($exists) {
            $style->title("Updating auth.json");
            $json = json_decode(file_get_contents($file), true);
        } else {
            $style->title("Creating auth.json");
            $json = [];
        }

        if (! array_key_exists('github-oauth', $json)) {
            $json['github-oauth'] = [];
        }

        $json['github-oauth']['github.com'] = $data->getGitHubOAuthToken();

        if (! array_key_exists('http-basic', $json)) {
            $json['http-basic'] = [];
        }

        $json['http-basic']['repo.magento.com'] = [
            'username' => $data->getMagentoPublicKey(),
            'password' => $data->getMagentoPrivateKey()
        ];

        $json = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $this->template->write($file, $json);

        $style->success($exists ? 'Updated auth.json' : 'Created auth.json');
    }
}
