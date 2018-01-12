<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\CommandLine;
use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class ComposerJson implements StepInterface
{
    const REPO_PHPCS = 'git@github.com:WeareJH/php-coding-standards.git';

    /**
     * @var CommandLine
     */
    private $cl;

    /**
     * @var Template
     */
    private $template;

    public function __construct(CommandLine $cl, Template $template)
    {
        $this->cl = $cl;
        $this->template = $template;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $this->updateJson($data, $style);
        $this->updateLock($data, $style);
    }

    private function updateJson(DataInterface $data, StyleInterface $style)
    {
        $path = $data->getPath();
        $file = "{$path}/composer.json";

        $exist = file_exists($file);

        if ($exist) {
            $style->title('Updating composer.json');
            $json = json_decode(file_get_contents($file), true);
        } else {
            $style->title('Creating composer.json');
            $json = [];
        }

        $ns = $data->getProjectNamespace();

        $json['name']        = strtolower($ns) . '-magento2';
        $json['description'] = 'eCommerce Platform for ' . $data->getProjectName();

        $cs = array_filter($json['repositories'], function ($repo) {
            return $repo['url'] == self::REPO_PHPCS;
        });
        if (1 > count($cs)) {
            $json['repositories'][] = [
                'type' => 'vcs',
                'url' => self::REPO_PHPCS
            ];
        }

        $json['repositories'] = array_values($json['repositories']);

        $json['scripts'] = [
            'bootstrap'  => 'composer install -o --prefer-dist --ignore-platform-reqs',
            'coverage'   => 'phpunit --coverage-text',
            'cs'         => "phpcs -s app/code/{$ns} --standard=vendor/wearejh/php-coding-standards/Jh",
            'cs-fix'     => "phpcbf -s app/code/{$ns} --standard=vendor/wearejh/php-coding-standards/Jh",
            'test'       => ['@cs', '@unit-tests'],
            'unit-tests' => 'phpunit',
        ];

        $req = [
            'phpunit/phpunit' => '^6.0',
            'squizlabs/php_codesniffer' => '^3.0',
            'wearejh/m2-module-symlink-assets' => '^1.0',
            'wearejh/php-coding-standards' => 'dev-master',
        ];
        if (array_key_exists('require-dev', $json)) {
            $json['require-dev'] = array_merge($json['require-dev'], $req);
        } else {
            $json['require-dev'] = $req;
        }

        $json = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $this->template->write($file, $json);

        $style->success($exist ? 'Updated composer.json' : 'Created composer.json');
    }

    private function updateLock(DataInterface $data, StyleInterface $style)
    {
        $style->title('Updating composer.lock');

        $path = $data->getPath();
        $quiet = false ? '-q' : '';

        $command = <<<SHELL
cd {$path} \
&& composer update --ignore-platform-reqs --prefer-dist {$quiet}
SHELL;

        $this->cl->run($command);
        $style->success('Updated composer.lock');
    }
}
