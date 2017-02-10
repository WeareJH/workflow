<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Docker implements StepInterface
{
    /**
     * @var TemplateWriter
     */
    private $templateWriter;

    public function __construct(TemplateWriter $templateWriter)
    {
        $this->templateWriter = $templateWriter;
    }
    
    public function run(Details $details, OutputStyle $output)
    {
        $output->success('Setting up Docker');

        $this->setupRequiredDirectories($details);
        $this->copyFiles($details);
        $this->setupNginx($details);
        $this->setupMagentoInstallBinary($details);
        $this->setupComposeTemplates($details);
    }

    private function setupRequiredDirectories(Details $details)
    {
        $reqDirs = [
            sprintf('.docker/certs/live/%s', $details->getProjectDomain()),
            '.docker/db',
            '.docker/nginx/sites',
            '.docker/php/bin',
            '.docker/php/etc',
        ];

        foreach ($reqDirs as $dir) {
            mkdir(sprintf('%s/%s', $details->getProjectName(), $dir), 0777, true);
        }

        touch($details->getProjectName() . '/.docker/db/.gitkeep');
    }

    private function copyFiles(Details $details)
    {
        $files = [
            'env/local.env.dist'        => '.docker/local.env.dist',
            'env/production.env.dist'   => '.docker/production.env.dist',
            'app.php.dockerfile'        => 'app.php.dockerfile',
            '.dockerignore'             => '.dockerignore',
            'php/bin/docker-configure'  => '.docker/php/bin/docker-configure',
            'php/bin/magento-configure' => '.docker/php/bin/magento-configure',
            'php/etc/custom.template'   => '.docker/php/etc/custom.template',
            'php/etc/msmtprc.template'  => '.docker/php/etc/msmtprc.template',
            'php/etc/xdebug.template'   => '.docker/php/etc/xdebug.template',
            'certs/cert.pem'            => sprintf('.docker/certs/live/%s/cert.pem', $details->getProjectDomain()),
            'certs/privkey.pem'         => sprintf('.docker/certs/live/%s/privkey.pem', $details->getProjectDomain()),
        ];

        foreach ($files as $templatePath => $projectPath) {
            $this->templateWriter->copyTemplate(
                $details->getProjectName(),
                'docker/' . $templatePath,
                $projectPath
            );
        }
    }

    private function setupNginx(Details $details)
    {
        $this->templateWriter->fillAndWriteTemplate(
            $details->getProjectName(),
            'docker/nginx/site.conf',
            '.docker/nginx/sites/site.conf',
            [
                'project-url' => $details->getProjectDomain()
            ]
        );
    }

    private function setupMagentoInstallBinary(Details $details)
    {
        $regex = $details->includeRabbitMQ()
            ? '/##RABBIT/s'
            : '/##RABBIT(\n.*)*##RABBIT/s';

        $this->templateWriter->regexFillAndWriteTemplate(
            $details->getProjectName(),
            'docker/php/bin/magento-install',
            '.docker/php/bin/magento-install',
            [
                $regex => ''
            ]
        );
    }

    private function setupComposeTemplates(Details $details)
    {
        $composeFiles = [
            'docker-compose.yml',
            'docker-compose.dev.yml',
            'docker-compose.prod.yml',
        ];

        foreach ($composeFiles as $composeFile) {
            $this->templateWriter->fillAndWriteTemplate(
                $details->getProjectName(),
                'docker/' . $composeFile,
                $composeFile,
                [
                    'project-name' => $details->getProjectName(),
                    'use-rabbit'   => $details->includeRabbitMQ() ? '' : '#'
                ]
            );
        }
    }
}
