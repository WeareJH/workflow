<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Docker implements StepInterface
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
        $style->title('Adding Docker configuration');

        $path = $data->getPath();
        $domain = $data->getProjectDomain();

        $cp = [
            'docker/.dockerignore'             => '/.dockerignore',
            'docker/app.php.dockerfile'        => '/app.php.dockerfile',
            'docker/certs/cert.pem'            => "/.docker/certs/live/{$domain}/cert.pem",
            'docker/certs/privkey.pem'         => "/.docker/certs/live/{$domain}/privkey.pem",
            'docker/env/local.env.dist'        => 'docker/env/local.env.dist',
            'docker/env/production.env.dist'   => '/.docker/production.env.dist',
            'docker/php/bin/docker-configure'  => '/.docker/php/bin/docker-configure',
            'docker/php/bin/magento-configure' => '/.docker/php/bin/magento-configure',
            'docker/php/etc/custom.template'   => '/.docker/php/etc/custom.template',
            'docker/php/etc/msmtprc.template'  => '/.docker/php/etc/msmtprc.template',
            'docker/php/etc/xdebug.template'   => '/.docker/php/etc/xdebug.template',
        ];
        foreach ($cp as $template => $dest) {
            $this->template->cp($template, "{$path}/{$dest}");
        }

        // @todo autogenerate local.env
        $this->template->repcp('docker/env/local.env.dist', "{$path}/.docker/local.env", [
            'project-domain' => $domain
        ]);

        $this->template->repcp('docker/nginx/site.conf', "{$path}/.docker/nginx/sites/site.conf", [
            'project-url' => $domain
        ]);

        // @todo this does not work because duh, shell scripts won't break
        // over comment lines automatically. so the original regex method was
        // better, except for the 'escaped whitespace' bug. to fix properly
        // another time
        $this->template->repcp('docker/php/bin/magento-install', "{$path}/.docker/php/bin/magento-install", [
            'use-rabbit' => $data->getUseRabbitMQ() ? '' : '#'
        ]);

        $rcp = [
            'docker/docker-compose.dev.yml'  => 'docker-compose.dev.yml',
            'docker/docker-compose.prod.yml' => 'docker-compose.prod.yml',
            'docker/docker-compose.yml'      => 'docker-compose.yml',
        ];
        $replace = [
            'project-name' => strtolower($data->getProjectNamespace()),
            'use-rabbit'   => $rabbit ? '' : '#'
        ];
        foreach ($rcp as $template => $dest) {
            $this->template->repcp($template, "{$path}/{$dest}", $replace);
        }

        $style->success('Added Docker configuration');
    }
}
