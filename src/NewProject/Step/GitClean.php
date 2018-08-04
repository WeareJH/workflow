<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Details;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GitClean implements StepInterface
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
        $output->success('Cleaning your host');

        $cwd = getcwd();
        chdir($details->getProjectName());

        $this->commandLine->runQuietly('git clean -dfX');

        chdir($cwd);
    }
}
