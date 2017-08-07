<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GitCommit implements StepInterface
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
        $output->success('Commit config and pushing to repo');

        $cwd = getcwd();
        chdir($details->getProjectName());

        $command =  'git add .';
        $command .= ' && git commit -m "Project Config"';
        $command .= ' && git push origin master';

        $this->commandLine->runQuietly($command);

        chdir($cwd);
    }
}
