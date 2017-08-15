<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class CreateProject implements StepInterface
{
    /**
     * @var TemplateWriter
     */
    private $templateWriter;

    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine, TemplateWriter $templateWriter)
    {
        $this->templateWriter = $templateWriter;
        $this->commandLine = $commandLine;
    }

    public function run(Details $details, OutputStyle $output)
    {
        $output->success(sprintf('Running composer create-project into %s', $details->getProjectName()));

        $cmdFormat =  'composer create-project -q --repository-url=https://%s:%s@repo.magento.com/ ';
        $cmdFormat .= 'magento/project-%s-edition %s --prefer-dist --ignore-platform-reqs';

        $command = sprintf(
            $cmdFormat,
            $details->getPubKey(),
            $details->getPrivKey(),
            $details->getVersion(),
            $details->getProjectName()
        );

        $this->commandLine->run($command);

        $filesToRemove = [
            '/ISSUE_TEMPLATE.md',
            '/nginx.conf.sample',
            '/php.ini.sample',
            '/package.json.sample',
            '/LICENSE.txt',
            '/LICENSE_AFL.txt',
            '/LICENSE_EE.txt',
            '/README_EE.txt',
            '/Gruntfile.js.sample',
            '/CHANGELOG.md',
            '/CONTRIBUTING.md',
            '/CHANGELOG.md',
            '/.travis.yml',
            '/.php_cs',
            '/.htaccess.sample',
            '/.htaccess',
        ];

        foreach ($filesToRemove as $file) {
            $file = $details->getProjectName() . $file;

            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->templateWriter->copyTemplate($details->getProjectName(), 'git/.gitignore', '.gitignore');

        $output->success(sprintf('Creating code directory app/code/%s', $details->getNamespace()));

        mkdir($details->getProjectName() . '/app/code/' . $details->getNamespace(), 0777, true);
        touch($details->getProjectName() . '/app/code/' . $details->getNamespace() . '/.gitkeep');
    }
}
