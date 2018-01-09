<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\ProcessFactory;
use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ComposerJson implements StepInterface
{
    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        $this->commandLine = $commandLine;
    }

    public function run(Details $details, OutputStyle $output)
    {
        $output->success('Setting up composer.json including CS scripts');

        $data        = json_decode(file_get_contents($details->getProjectName() . '/composer.json'), true);
        $csFormat    = 'phpcs -s app/code/%s --standard=vendor/wearejh/php-coding-standards/Jh';
        $csFixFormat = 'phpcbf -s app/code/%s --standard=vendor/wearejh/php-coding-standards/Jh';

        $data['name']                = $details->getProjectName() . '-magento2';
        $data['description']         = 'eCommerce Platform for ' . $details->getProjectName();
        $data['repositories'][]      = ['type' => 'vcs', 'url' => 'git@github.com:WeareJH/php-coding-standards.git'];

        $data['scripts'] = [
            'test'       => ['@cs', '@unit-tests'],
            'cs'         => sprintf($csFormat, $details->getNamespace()),
            'cs-fix'     => sprintf($csFixFormat, $details->getNamespace()),
            'unit-tests' => 'phpunit',
            'coverage'   => 'phpunit --coverage-text',
            'bootstrap'  => 'composer install -o --prefer-dist --ignore-platform-reqs'
        ];

        $data['require-dev']['wearejh/php-coding-standards']     =  'dev-master';
        $data['require-dev']['wearejh/m2-module-symlink-assets'] =  '^1.0';
        $data['require-dev']['squizlabs/php_codesniffer'] = '^3.0';
        $data['require-dev']['phpunit/phpunit'] = '^6.0';

        file_put_contents(
            $details->getProjectName() . '/composer.json',
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        $cwd = getcwd();
        chdir($details->getProjectName());

        $output->success('Updating composer lock file');
        try {
            $command =  'docker run --rm ';
            $command .= '-v %s:/root/build -v %s/.composer/cache:/root/.composer/cache ';
            $command .= 'wearejh/ci-build-env composer update --prefer-dist -qo';

            $this->commandLine->run(sprintf($command, getcwd(), getenv('HOME')));
        } catch (ProcessFailedException $e) {
            throw new \RuntimeException('Could not update composer lock file');
        }

        chdir($cwd);
    }
}
